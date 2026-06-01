<?php

namespace App\Http\Requests;

use App\Rules\CleanWords;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
                // gestion des injection XSS.
                'content' => strip_tags(trim($this->input('content'))),
            ]);
        }
    }

    public function rules(): array
    {
        $forbidden = config('validation.forbidden_content', []);

        return [
            'content' => [
                'required',
                'string',
                'min:1',
                'max:2000', // On evite la saturation
                new CleanWords($forbidden), // gestion des gros mots et mots trompeurs
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2Mo max
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Le contenu du post est obligatoire.',
            'content.string' => 'Le contenu doit être une chaîne de caractères.',
            'content.min' => 'Le contenu doit comporter au moins :min caractère.',
            'content.max' => 'Le contenu ne peut pas dépasser :max caractères.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format :values.',
            'image.max' => 'L\'image ne peut pas dépasser :max kilobytes.',
        ];
    }
}
