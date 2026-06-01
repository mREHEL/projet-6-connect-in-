<?php

namespace App\Http\Requests;

use App\Rules\CleanWords;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Ne nettoie que si le contenu existe
        if ($this->has('content') && $this->input('content') !== null) {
            $this->merge([
                // On nettoie les balises HTML
                'content' => strip_tags(trim($this->input('content'))),
            ]);
        }
    }

    public function rules(): array
    {
        // On utilise la liste de mots interdits pour le contenu
        $forbidden = config('validation.forbidden_content', []);

        return [
            'content' => [
                'required',
                'string',
                'min:1',
                'max:1000', // On limite la taille pour la saturation
                new CleanWords($forbidden),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Le commentaire ne peut pas être vide.',
            'content.max' => 'Le commentaire est trop long (maximum 1000 caractères).',
        ];
    }
}
