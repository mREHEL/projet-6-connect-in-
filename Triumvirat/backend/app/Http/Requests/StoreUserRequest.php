<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use App\Rules\CleanWords;


class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Tout le monde peut s'inscrire
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => strtolower(trim($this->username)),
            'email' => strtolower(trim($this->email)),
            'first_name' => strtolower(trim($this->first_name)),
            'last_name' => strtolower(trim($this->last_name)),
        ]);
    }

    public function rules(): array
    {
        $reservedEmails = array_map(fn($word) => $word . '@epitech.eu', config('validation.forbidden_email_terms', []));

        return [
            'username' => [
                'required',
                'string',
                'alpha_dash',
                'min:3',
                'max:30',
                'unique:users,username',
                new CleanWords(config('validation.forbidden_identifiers')),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@epitech\.eu$/i',
                Rule::notIn($reservedEmails),
                new CleanWords(config('validation.forbidden_email_terms')),
            ],
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/u',
                new CleanWords(config('validation.forbidden_identifiers')),

            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/u',

                new CleanWords(config('validation.forbidden_identifiers')),
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers()->mixedCase()->symbols(),
                new CleanWords(config('validation.forbidden_passwords')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Ce pseudo est déjà pris, choisis-en un autre !',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit comporter au moins :min caractères.',
            'password.letters' => 'Le mot de passe doit contenir au moins une lettre.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.mixedCase' => 'Le mot de passe doit contenir des lettres majuscules et minuscules.',
            'email.regex' => 'L\'email doit être une adresse e-mail valide se terminant par @epitech.eu',
            'first_name.min' => 'Le prénom doit comporter au moins :min caractères.',
            'last_name.min' => 'Le nom doit comporter au moins :min caractères.',
            'first_name.max' => 'Le prénom ne peut pas dépasser :max caractères.',
            'last_name.max' => 'Le nom ne peut pas dépasser :max caractères.',
            'last_name.required' => 'Le nom est obligatoire.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse e-mail valide.',
            'email.max' => 'L\'email ne peut pas dépasser :max caractères.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'username.required' => 'Le pseudo est obligatoire.',
        ];
    }
}
