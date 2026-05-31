<?php

declare(strict_types=1);

/**
 * Validátor formulářových dat.
 * * Kompletní implementace pro 2. ročník IT dle zadání.
 */
final class Validator {

    /** @var array<string, string> pole chyb (klíč = název pole, hodnota = chybová hláška) */
    private array $errors = [];

    /**
     * Pole nesmí být prázdné (po oříznutí mezer).
     */
    public function required(string $field, string $value, string $message): self {
        if (trim($value) === '') {
            $this->errors[$field] ??= $message;
        }
        return $this;
    }

    /**
     * Hodnota musí být platný e-mail.
     * Validuje pouze pokud hodnota není prázdná.
     */
    public function email(string $field, string $value, string $message): self {
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$field] ??= $message;
        }
        return $this;
    }

    /**
     * Hodnota musí mít minimální délku.
     * Validuje pouze pokud hodnota není prázdná.
     */
    public function minLength(string $field, string $value, int $min, string $message): self {
        if ($value !== '' && mb_strlen($value) < $min) {
            $this->errors[$field] ??= $message;
        }
        return $this;
    }

    /**
     * Hodnota nesmí překročit maximální délku.
     */
    public function maxLength(string $field, string $value, int $max, string $message): self {
        if ($value !== '' && mb_strlen($value) > $max) {
            $this->errors[$field] ??= $message;
        }
        return $this;
    }

    /**
     * Hodnota musí odpovídat regulárnímu výrazu.
     * Validuje pouze pokud hodnota není prázdná.
     */
    public function pattern(string $field, string $value, string $regex, string $message): self {
        if ($value !== '' && preg_match($regex, $value) !== 1) {
            $this->errors[$field] ??= $message;
        }
        return $this;
    }

    /**
     * Hodnota musí být jedno z povolených hodnot.
     */
    public function in(string $field, string|int $value, array $allowed, string $message): self {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] ??= $message;
        }
        return $this;
    }

    /**
     * Jsou data validní (žádné chyby)?
     */
    public function isValid(): bool {
        return empty($this->errors);
    }

    /**
     * Vrátí všechny chyby.
     *
     * @return array<string, string>
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Vrátí chybu pro konkrétní pole (nebo null, pokud pole nemá chybu).
     */
    public function getError(string $field): ?string {
        return $this->errors[$field] ?? null;
    }

    /**
     * Má dané pole chybu?
     */
    public function hasError(string $field): bool {
        return isset($this->errors[$field]);
    }
}