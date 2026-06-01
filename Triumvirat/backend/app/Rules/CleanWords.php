<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 *  Toute cette page sert à empêcher les utilisateurs d'utiliser des mots interdits, même avec des astuces pour les contourner (espaces, points, tirets, etc.)
 * En allant chercher les mots interdits dans la config, on peut facilement les mettre à jour sans toucher au code.
 */

class CleanWords implements ValidationRule
{
    protected array $forbidden;
    protected ?string $matchedWord = null;

    public function __construct(array $forbidden)
    {
        $this->forbidden = array_filter(
            array_map('trim', array_map('strtolower', $forbidden)),
            fn($word) => strlen($word) >= 2 // Ignore les mots trop courts
        );
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (empty($value)) {
            return;
        }

        $cleanValue = $this->sanitize($value);

        foreach ($this->forbidden as $word) {

            if ($this->containsForbiddenWord($cleanValue, $word)) {
                $this->matchedWord = $word;
                $fail("Le :attribute contient un terme non autorisé.");
                return;
            }
        }
    }

    /**
     * Nettoie la valeur pour la comparaison
     */
    protected function sanitize(string $value): string
    {
        // Retire les caractères spéciaux utilisés pour contourner le filtre
        $cleaned = preg_replace('/[\s\.\-_]+/', '', $value);

        return strtolower($cleaned);
    }

    /**
     * Vérifie si un mot interdit est présent
     */
    protected function containsForbiddenWord(string $haystack, string $needle): bool
    {
        // Vérification stricte: le mot interdit doit être isolé ou avec des séparateurs
        $letters = str_split($needle);
        $pattern = '/\b' . implode('[\s\.\-_]*', array_map('preg_quote', $letters)) . '\b/iu';

        // Aussi vérifier dans la version nettoyée (sans espaces)
        return str_contains($haystack, $needle) || preg_match($pattern, $haystack);
    }

    /**
     * Message d'erreur personnalisé (optionnel, pour debug)
     */
    public function getMatchedWord(): ?string
    {
        return $this->matchedWord;
    }
}