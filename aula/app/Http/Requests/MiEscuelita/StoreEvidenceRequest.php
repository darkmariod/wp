<?php

namespace App\Http\Requests\MiEscuelita;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización real (¿es SU hijo? ¿pide evidencia?) vive en
        // EvidencePolicy::create y se llama en el controller, donde ya
        // hay $content y $child disponibles.
        return true;
    }

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
}
