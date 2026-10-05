<?php

namespace App\Services\Biblioteca;

use App\Models\ResourceFile;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Arma la respuesta que entrega un archivo guardado. NO autoriza: quien la
 * llama (controlador web o API) debe haber comprobado ya el acceso.
 *
 * Reglas fijas: el archivo sale del disco guardado en su propia fila (no del
 * configurado hoy), el tipo de contenido es el guardado al subirlo (nunca el
 * de la petición ni el del nombre) y ninguna respuesta revela la ruta.
 */
class ResourceDelivery
{
    /**
     * Cabeceras de toda entrega. Sin `Content-Security-Policy: sandbox`: rompe
     * el visor de PDF del navegador.
     */
    private const SECURITY_HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'Cache-Control' => 'private, no-store',
        'Referrer-Policy' => 'no-referrer',
        'Cross-Origin-Resource-Policy' => 'same-origin',
    ];

    /**
     * Para el visor de la página: solo contenido pasivo. Un tipo activo
     * (html, svg, xml, js...) guardado por error jamás se muestra en línea.
     */
    private const PASSIVE_INLINE_MIME = '#^(application/(pdf|ogg)|(image|audio|video)/(?!svg)[a-z0-9.+-]+)$#';

    public function inline(ResourceFile $file, Request $request): Response
    {
        abort_unless(preg_match(self::PASSIVE_INLINE_MIME, $this->contentType($file)) === 1, 404);

        return $this->deliver($file, $request, HeaderUtils::DISPOSITION_INLINE);
    }

    public function download(ResourceFile $file, Request $request): Response
    {
        return $this->deliver($file, $request, HeaderUtils::DISPOSITION_ATTACHMENT);
    }

    private function deliver(ResourceFile $file, Request $request, string $disposition): Response
    {
        $disk = Storage::disk($file->disk);

        if (! $disk->exists($file->path)) {
            // Para operaciones: la respuesta al usuario es un 404 sin ruta.
            report(new FileNotFoundException("Biblioteca: falta el objeto del archivo {$file->id} (recurso {$file->resource_id}) en el disco {$file->disk}."));

            abort(404);
        }

        $contentType = $this->contentType($file);
        $contentDisposition = $this->contentDisposition($file, $disposition);

        return $this->isLocal($disk)
            ? $this->local($disk, $file, $request, $contentType, $contentDisposition)
            : $this->remote($disk, $file, $contentType, $contentDisposition);
    }

    /**
     * Disco local: se sirve desde la app. BinaryFileResponse atiende Range
     * (video y audio con adelantar), ETag y peticiones condicionales.
     */
    private function local(FilesystemAdapter $disk, ResourceFile $file, Request $request, string $contentType, string $contentDisposition): Response
    {
        $response = new BinaryFileResponse(
            $disk->path($file->path),
            headers: [...self::SECURITY_HEADERS, 'Content-Type' => $contentType, 'Content-Disposition' => $contentDisposition],
            public: false,
        );

        // Los objetos nunca se reescriben (nombre generado): id, tamaño y fecha
        // bastan y evitan leer un video completo para calcular su hash.
        $response->setEtag(hash('xxh128', "{$file->id}|{$file->size}|{$response->getFile()->getMTime()}"));
        $response->isNotModified($request);

        return $response;
    }

    /**
     * Disco remoto (s3 / R2): redirige a una URL firmada de vida corta que ya
     * lleva el tipo y la disposición; el archivo no pasa por la app.
     */
    private function remote(FilesystemAdapter $disk, ResourceFile $file, string $contentType, string $contentDisposition): Response
    {
        $url = $disk->temporaryUrl(
            $file->path,
            now()->addMinutes(max(1, (int) config('biblioteca.temporary_url_minutes', 5))),
            [
                'ResponseContentType' => $contentType,
                'ResponseContentDisposition' => $contentDisposition,
                'ResponseCacheControl' => self::SECURITY_HEADERS['Cache-Control'],
            ],
        );

        return redirect()->away($url, 302, self::SECURITY_HEADERS);
    }

    private function isLocal(FilesystemAdapter $disk): bool
    {
        return $disk->getAdapter() instanceof LocalFilesystemAdapter;
    }

    /**
     * Tipo guardado al subir (ya validado por contenido contra la lista de
     * permitidos). Si faltara, un tipo inerte: con nosniff no se interpreta.
     */
    private function contentType(ResourceFile $file): string
    {
        return strtolower(trim((string) $file->mime_type)) ?: 'application/octet-stream';
    }

    private function contentDisposition(ResourceFile $file, string $disposition): string
    {
        if ($disposition === HeaderUtils::DISPOSITION_INLINE) {
            return HeaderUtils::DISPOSITION_INLINE;
        }

        $name = $this->displayName($file);
        $fallback = (string) preg_replace('/[^\x20-\x7E]|[%\/\\\\]/', '_', Str::ascii($name));

        // makeDisposition codifica los nombres con tildes (filename*=utf-8'').
        return HeaderUtils::makeDisposition($disposition, $name, $fallback);
    }

    /**
     * El nombre original ya se sanea al subir; aquí se vuelve a asegurar que
     * no lleve rutas ni caracteres de control, porque va dentro de una cabecera.
     */
    private function displayName(ResourceFile $file): string
    {
        $name = basename(str_replace('\\', '/', mb_scrub((string) $file->original_name)));
        $name = trim((string) preg_replace('/[\p{Cc}\p{Cf}]+/u', '', $name));

        return $name === '' ? 'archivo.'.($file->extension ?: 'bin') : $name;
    }
}
