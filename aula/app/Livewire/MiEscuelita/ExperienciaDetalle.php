<?php

namespace App\Livewire\MiEscuelita;

use App\Http\Controllers\MiEscuelita\Concerns\ResolvesCurrentChild;
use App\Models\Child;
use App\Models\Content;
use App\Models\Evidence;
use App\Models\Media;
use App\Notifications\EvidenceSubmittedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ExperienciaDetalle extends Component
{
    use ResolvesCurrentChild;
    use WithFileUploads;

    public Content $content;

    public bool $yaEnviada;

    public bool $esPrevia = false;

    public $child_id;

    public $comment = '';

    /** @var array<int, TemporaryUploadedFile> */
    public $fotos = [];

    public $video;

    public $document;

    public function mount(Content $content, bool $yaEnviada, bool $esPrevia = false): void
    {
        $this->content = $content;
        $this->yaEnviada = $yaEnviada;
        $this->esPrevia = $esPrevia;
        $this->child_id = $this->currentChild()?->id;
    }

    #[Computed]
    public function hijosActivos(): Collection
    {
        return Auth::user()?->family?->children()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ?? collect();
    }

    /**
     * El formulario solo se muestra si la experiencia pide evidencia,
     * está publicada, el usuario es familia, todavía no la envió y no
     * está en modo vista previa. Volver a validar acá (además de la
     * Policy en el submit) evita confiar en "siempre se oculta solo".
     */
    #[Computed]
    public function mostrarFormulario(): bool
    {
        return $this->content->requires_evidence
            && $this->content->status === Content::STATUS_PUBLISHED
            && ! $this->esPrevia
            && ! $this->yaEnviada
            && Auth::user()?->isFamilia() === true;
    }

    /**
     * Fecha legible del envío cuando ya se compartió esta experiencia.
     */
    #[Computed]
    public function fechaEnvio(): ?string
    {
        if (! $this->yaEnviada) {
            return null;
        }

        $child = $this->currentChild();

        if (! $child) {
            return null;
        }

        $evidencia = $this->content->evidence()
            ->where('child_id', $child->id)
            ->orderByDesc('submitted_at')
            ->first();

        return $evidencia?->submitted_at?->locale('es')->isoFormat('D [de] MMMM');
    }

    /**
     * Mismas reglas exactas que StoreEvidenceRequest.
     */
    public function rules(): array
    {
        return [
            'child_id' => ['required', 'integer', 'exists:children,id'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'max:8192'], // 8 MB por foto
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime', 'max:51200'], // 50 MB
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.max' => 'Se pueden compartir hasta 5 fotos.',
            'photos.*.image' => 'Cada archivo de foto debe ser una imagen.',
            'video.mimetypes' => 'El video debe ser MP4 o MOV.',
        ];
    }

    public function tamanoHumano(?int $bytes): string
    {
        if (! $bytes) {
            return '';
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$unidades[$i];
    }

    public function quitarFoto(int $index): void
    {
        unset($this->fotos[$index]);
        $this->fotos = array_values($this->fotos);
    }

    public function quitarVideo(): void
    {
        $this->video = null;
    }

    public function quitarDocumento(): void
    {
        $this->document = null;
    }

    /**
     * Reemplaza el POST del controller con el mismo flujo y las mismas
     * garantías: rate limit propio (Livewire postea a /livewire/update,
     * el throttle de ruta ya no corre), Gate::authorize obligatorio antes
     * de crear, transacción, media en disco privado y notificación.
     */
    public function submit(): void
    {
        $user = Auth::user();

        $key = 'evidencia:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('child_id', "Demasiados intentos. Intentá de nuevo en {$seconds} segundos.");

            return;
        }

        RateLimiter::hit($key, 60);

        $this->validate();

        $child = Child::findOrFail($this->child_id);

        // No negociable: la misma Policy que protege el POST del
        // controller. No alcanza con esconder el botón.
        Gate::authorize('create', [Evidence::class, $this->content, $child]);

        $evidence = DB::transaction(function () use ($child, $user) {
            $evidence = Evidence::create([
                'content_id' => $this->content->id,
                'child_id' => $child->id,
                'family_id' => $user->family_id,
                'comment' => $this->comment,
                'status' => Evidence::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            foreach ($this->fotos as $foto) {
                $this->attachMedia($evidence, $foto, Media::TYPE_IMAGE);
            }

            if ($this->video) {
                $this->attachMedia($evidence, $this->video, Media::TYPE_VIDEO);
            }

            if ($this->document) {
                $ext = strtolower($this->document->getClientOriginalExtension());
                $type = $ext === 'pdf' ? Media::TYPE_PDF : Media::TYPE_DOCUMENT;
                $this->attachMedia($evidence, $this->document, $type);
            }

            return $evidence;
        });

        $this->content->teacher->notify(new EvidenceSubmittedNotification($evidence));

        session()->flash('success', 'Experiencia compartida. Gracias por compartir este momento con nosotros.');

        $this->redirectRoute('mi-escuelita.experiencias.show', $this->content->slug);
    }

    /**
     * Guarda el archivo en el disco PRIVADO (storage/app/private, sin
     * symlink público) y registra el Media apuntando a esa ruta — nunca
     * a algo servible directo por URL.
     */
    private function attachMedia(Evidence $evidence, $file, string $type): void
    {
        $path = $file->store('evidence/'.$evidence->id, 'local');

        $evidence->media()->create([
            'type' => $type,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function render(): View
    {
        return view('livewire.mi-escuelita.experiencia-detalle');
    }
}
