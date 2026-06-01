<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use App\Rules\CleanWords;
use Illuminate\Support\Facades\Hash;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $inputsToClean = ['username', 'email', 'first_name', 'last_name'];
        $updates = [];

        foreach ($inputsToClean as $field) {
            if ($this->has($field)) {
                $updates[$field] = strtolower(trim($this->$field));
            }
        }

        if (!empty($updates)) {
            $this->merge($updates);
        }
    }

    public function rules(): array
    {
        $userId = $this->user()->id;


        return [
            'username' => [
                'sometimes',
                'required', // Si le champ est présent, il ne doit pas être vide
                'string',
                'alpha_dash',
                'min:3',
                'max:30',
                Rule::unique('users')->ignore($userId),
                Rule::notIn(config('validation.forbidden_identifiers', [])),
                new CleanWords(config('validation.forbidden_identifiers', [])),
            ],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@epitech\.eu$/i',
                Rule::unique('users')->ignore($userId),
                Rule::notIn(array_map(fn($w) => "$w@epitech.eu", config('validation.forbidden_email_terms', []))),
                new CleanWords(config('validation.forbidden_email_terms', [])),
            ],

            'current_password' => [
                'required_with:password',
                'string',
            ],

            'password' => [
                'nullable',
                'confirmed',
                'min:8', // Force une longueur même si nullable
                'different:current_password',
                Password::min(8)->letters()->numbers()->mixedCase()->symbols(),
            ],

            'first_name' => [
                'sometimes',
                'required', // Empêche d'envoyer un prénom vide
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/u',
                new CleanWords(config('validation.forbidden_identifiers', [])),
            ],

            'last_name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/u',
                new CleanWords(config('validation.forbidden_identifiers', [])),
            ],

            'bio' => [
                'nullable', // La bio peut être vidée (c'est un choix utilisateur)
                'string',
                'max:1000',
                new CleanWords(config('validation.forbidden_content', [])),
            ],

            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'delete_profile_photo' => 'nullable|boolean',
            'delete_cover_image' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérification du mot de passe actuel
            if ($this->filled('password') && $this->filled('current_password')) {
                if (!Hash::check($this->current_password, $this->user()->password)) {
                    $validator->errors()->add('current_password', 'Le mot de passe actuel est incorrect.');
                }
            }

            // Sécurité Upload vs Delete
            $this->checkConflict($validator, 'profile_photo', 'delete_profile_photo');
            $this->checkConflict($validator, 'cover_image', 'delete_cover_image');
        });
    }

    private function checkConflict($validator, $fileField, $deleteField)
    {
        if ($this->boolean($deleteField) && $this->hasFile($fileField)) {
            $validator->errors()->add($fileField, "Vous ne pouvez pas supprimer et uploader une image en même temps.");
        }
    }
    public function messages(): array
    {
        return [
            // Username
            'username.unique' => 'Ce pseudo est déjà utilisé.',
            'username.alpha_dash' => 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores.',
            'username.min' => 'Le pseudo doit contenir au moins :min caractères.',
            'username.max' => 'Le pseudo ne peut pas dépasser :max caractères.',

            // Email
            'email.unique' => 'Cette adresse email est déjà enregistrée.',
            'email.regex' => 'L\'email doit être une adresse @epitech.eu valide.',

            // Password
            'current_password.required_with' => 'Le mot de passe actuel est requis pour changer de mot de passe.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.different' => 'Le nouveau mot de passe doit être différent de l\'ancien.',

            // Names
            'first_name.regex' => 'Le prénom contient des caractères non autorisés.',
            'last_name.regex' => 'Le nom contient des caractères non autorisés.',

            // Images
            'profile_photo.max' => 'La photo de profil ne doit pas dépasser 2 Mo.',
            'profile_photo.dimensions' => 'La photo de profil doit faire au moins 100x100 pixels.',
            'cover_image.max' => 'La photo de couverture ne doit pas dépasser 5 Mo.',
            'cover_image.dimensions' => 'La photo de couverture doit faire au moins 400x200 pixels.',

            // Bio
            'bio.max' => 'La bio ne peut pas dépasser :max caractères.',
        ];
    }
}
