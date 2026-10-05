<?php

namespace App\Services\Biblioteca;

use App\Enums\ResourceType;
use App\Models\LibraryResource;
use App\Models\ResourceFile;
use Closure;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Throwable;

/**
 * Guarda y borra los archivos de la Biblioteca. Un archivo subido no es de
 * fiar: aquí se valida su CONTENIDO (no el nombre ni el tipo que declara el
 * cliente), se guarda en el disco privado con un nombre generado y el nombre
 * original queda solo como dato para mostrar.
 *
 * Las reglas que arma (rulesFor, thumbnailRules) son la única fuente de
 * verdad: los FormRequest, Filament y la API deben reutilizarlas.
 */
class ResourceFileService
{
    /**
     * Disco donde viven los archivos; siempre sale de la configuración.
     */
    public function diskName(): string
    {
        return (string) config('biblioteca.disk');
    }

    /**
     * Reglas de validación para el campo de archivo de un recurso de ese tipo.
     *
     * @return list<string|Closure>
     */
    public function rulesFor(ResourceType $type): array
    {
        if (! $type->usesFile()) {
            return ['prohibited'];
        }

        return $this->buildRules(
            $this->extensionsFor($type),
            $this->maxKbFor($type),
            $type->requiresFile() ? 'required' : 'nullable',
        );
    }

    /**
     * @return list<string|Closure>
     */
    public function thumbnailRules(): array
    {
        return $this->buildRules($this->thumbnailExtensions(), $this->thumbnailMaxKb(), 'required');
    }

    /**
     * Mensajes en español para las reglas de rulesFor(), ligados al campo indicado.
     *
     * @return array<string, string>
     */
    public function messagesFor(ResourceType $type, string $attribute = 'file'): array
    {
        return [
            ...$this->messages($attribute, $this->extensionsFor($type), $this->maxKbFor($type)),
            "{$attribute}.prohibited" => 'Los enlaces no llevan archivo adjunto.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function thumbnailMessages(string $attribute = 'file'): array
    {
        return $this->messages($attribute, $this->thumbnailExtensions(), $this->thumbnailMaxKb());
    }

    /**
     * Valida y guarda el archivo de un recurso. Lanza ValidationException si
     * no cumple las reglas de su tipo.
     */
    public function store(LibraryResource $resource, UploadedFile $file): ResourceFile
    {
        $this->validate($file, $this->rulesFor($resource->type), $this->messagesFor($resource->type));

        $extension = $this->extensionOf($file);
        $path = $this->put($file, "resources/{$resource->id}", $extension);

        try {
            return $resource->files()->create([
                'disk' => $this->diskName(),
                'path' => $path,
                'original_name' => $this->sanitizeName($file->getClientOriginalName(), $extension),
                'mime_type' => $this->sniffedMime($file),
                'size' => (int) $file->getSize(),
                'extension' => $extension,
            ]);
        } catch (Throwable $e) {
            // La fila no se creó: el objeto recién guardado quedaría huérfano.
            $this->deleteObject($this->diskName(), $path);

            throw $e;
        }
    }

    /**
     * Guarda el archivo nuevo PRIMERO y solo entonces borra los anteriores:
     * si el nuevo no sirve, el actual queda intacto.
     */
    public function replace(LibraryResource $resource, UploadedFile $file): ResourceFile
    {
        $anteriores = $resource->files()->get();

        $nuevo = $this->store($resource, $file);

        $anteriores->each(fn (ResourceFile $anterior) => $this->deleteFile($anterior));
        $resource->unsetRelation('files');

        return $nuevo;
    }

    /**
     * Guarda la miniatura del recurso y borra la anterior. Devuelve su ruta.
     */
    public function storeThumbnail(LibraryResource $resource, UploadedFile $file): string
    {
        $this->validate($file, $this->thumbnailRules(), $this->thumbnailMessages());

        $anterior = $resource->thumbnail;
        $path = $this->put($file, "thumbnails/{$resource->id}", $this->extensionOf($file));

        try {
            $resource->update(['thumbnail' => $path]);
        } catch (Throwable $e) {
            $this->deleteObject($this->diskName(), $path);

            throw $e;
        }

        if ($anterior) {
            $this->deleteObjectAfterCommit($this->diskName(), $anterior);
        }

        return $path;
    }

    public function deleteThumbnail(LibraryResource $resource): void
    {
        $path = $resource->thumbnail;

        if (! $path) {
            return;
        }

        $resource->update(['thumbnail' => null]);
        $this->deleteObjectAfterCommit($this->diskName(), $path);
    }

    /**
     * Borra la fila; ResourceFileObserver retira el objeto del disco.
     */
    public function deleteFile(ResourceFile $file): void
    {
        $file->delete();
    }

    /**
     * Borra todos los archivos del recurso y su miniatura (el recurso queda).
     */
    public function deleteAllFor(LibraryResource $resource): void
    {
        $resource->files()->get()->each(fn (ResourceFile $file) => $this->deleteFile($file));
        $resource->unsetRelation('files');

        $this->deleteThumbnail($resource);
    }

    /**
     * Retira un objeto del disco. Que ya no exista no es un error, y un fallo
     * del disco se reporta sin tumbar el borrado de la fila (el comando
     * biblioteca:limpiar-huerfanos recoge lo que quede).
     */
    public function deleteObject(string $disk, string $path): void
    {
        if ($path === '') {
            return;
        }

        try {
            Storage::disk($disk)->delete($path);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Igual que deleteObject, pero espera a que la transacción en curso se
     * confirme: si se revierte, el archivo sigue siendo necesario.
     */
    public function deleteObjectAfterCommit(string $disk, string $path): void
    {
        DB::afterCommit(fn () => $this->deleteObject($disk, $path));
    }

    private function disk(): Filesystem
    {
        return Storage::disk($this->diskName());
    }

    /**
     * @param  list<string>  $extensions
     * @return list<string|Closure>
     */
    private function buildRules(array $extensions, int $maxKb, string $presence): array
    {
        return [
            'bail',
            $presence,
            'file',
            $this->notEmptyRule(),
            'extensions:'.implode(',', $extensions),
            'mimetypes:'.implode(',', $this->mimesFor($extensions)),
            'max:'.$maxKb,
            $this->contentMatchesExtensionRule($extensions),
        ];
    }

    /**
     * @param  list<string>  $extensions
     * @return array<string, string>
     */
    private function messages(string $attribute, array $extensions, int $maxKb): array
    {
        $formatos = strtoupper(implode(', ', $extensions));

        return [
            "{$attribute}.required" => 'Selecciona un archivo.',
            "{$attribute}.file" => 'No se pudo recibir el archivo. Inténtalo de nuevo.',
            "{$attribute}.uploaded" => 'No se pudo recibir el archivo. Inténtalo de nuevo.',
            "{$attribute}.extensions" => "Solo se admiten archivos {$formatos}.",
            "{$attribute}.mimetypes" => "El contenido del archivo no corresponde a un formato permitido ({$formatos}).",
            "{$attribute}.max" => 'El archivo no puede pesar más de '.$this->formatSize($maxKb).'.',
        ];
    }

    /**
     * El rechazo del archivo vacío va antes de mirar su contenido: finfo
     * lo reporta como "x-empty" y el mensaje correcto es otro.
     */
    private function notEmptyRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value instanceof SymfonyUploadedFile && (int) $value->getSize() <= 0) {
                $fail('El archivo está vacío.');
            }
        };
    }

    /**
     * Cada extensión acepta solo SUS tipos de contenido: un PNG verdadero
     * no pasa como .jpg aunque ambos sean imágenes permitidas.
     *
     * @param  list<string>  $allowed
     */
    private function contentMatchesExtensionRule(array $allowed): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($allowed): void {
            if (! $value instanceof SymfonyUploadedFile) {
                return;
            }

            $extension = $this->extensionOf($value);

            if (! in_array($extension, $allowed, true)) {
                $fail('La extensión del archivo no está permitida.');

                return;
            }

            $accepted = array_map('strtolower', (array) config("biblioteca.extensions.{$extension}", []));

            if (! in_array($this->sniffedMime($value), $accepted, true)) {
                $fail("El contenido del archivo no corresponde a un .{$extension} válido.");
            }
        };
    }

    private function validate(UploadedFile $file, array $rules, array $messages): void
    {
        Validator::make(['file' => $file], ['file' => $rules], $messages)->validate();
    }

    /**
     * Guarda el archivo con un nombre generado. La extensión ya pasó la lista
     * de permitidas; nada más del nombre del cliente llega a la ruta.
     */
    private function put(UploadedFile $file, string $directory, string $extension): string
    {
        $path = $this->disk()->putFileAs($directory, $file, Str::uuid().'.'.$extension);

        if (! is_string($path)) {
            throw new RuntimeException('No se pudo guardar el archivo en el disco de la Biblioteca.');
        }

        return $path;
    }

    /**
     * @return list<string>
     */
    private function extensionsFor(ResourceType $type): array
    {
        return array_map('strtolower', (array) config("biblioteca.types.{$type->value}.extensions", []));
    }

    /**
     * @return list<string>
     */
    private function thumbnailExtensions(): array
    {
        return array_map('strtolower', (array) config('biblioteca.thumbnail.extensions', []));
    }

    private function maxKbFor(ResourceType $type): int
    {
        return $this->capKb((int) config("biblioteca.types.{$type->value}.max_kb"));
    }

    private function thumbnailMaxKb(): int
    {
        return $this->capKb((int) config('biblioteca.thumbnail.max_kb'));
    }

    /**
     * El tope global solo baja el máximo; un valor vacío o inválido se ignora.
     */
    private function capKb(int $kb): int
    {
        $tope = (int) config('biblioteca.max_upload_kb');

        return $tope > 0 ? min($kb, $tope) : $kb;
    }

    /**
     * Todos los tipos MIME aceptados por alguna de las extensiones.
     *
     * @param  list<string>  $extensions
     * @return list<string>
     */
    private function mimesFor(array $extensions): array
    {
        $mimes = [];

        foreach ($extensions as $extension) {
            array_push($mimes, ...(array) config("biblioteca.extensions.{$extension}", []));
        }

        return array_values(array_unique($mimes));
    }

    private function extensionOf(SymfonyUploadedFile $file): string
    {
        return strtolower($file->getClientOriginalExtension());
    }

    /**
     * Tipo MIME según el CONTENIDO (finfo), nunca el que declara el cliente.
     */
    private function sniffedMime(SymfonyUploadedFile $file): string
    {
        return strtolower((string) $file->getMimeType());
    }

    /**
     * Deja el nombre original apto para mostrarse: sin rutas, sin caracteres
     * de control ni de formato (como el que invierte el sentido del texto) y
     * con un máximo de 255 caracteres que conserva la extensión.
     */
    private function sanitizeName(string $name, string $extension): string
    {
        $name = basename(str_replace('\\', '/', mb_scrub($name)));
        $name = rtrim(trim((string) preg_replace('/[\p{Cc}\p{Cf}]+/u', '', $name)), '. ');

        if ($name === '' || pathinfo($name, PATHINFO_FILENAME) === '') {
            return "archivo.{$extension}";
        }

        if (mb_strlen($name) > 255) {
            $sufijo = ".{$extension}";
            $name = mb_substr($name, 0, 255 - mb_strlen($sufijo)).$sufijo;
        }

        return $name;
    }

    private function formatSize(int $kb): string
    {
        if ($kb < 1024) {
            return "{$kb} KB";
        }

        return rtrim(rtrim(number_format($kb / 1024, 1, ',', ''), '0'), ',').' MB';
    }
}
