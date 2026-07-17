<?php
/**
 * Input Validator
 */

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /* ── Rules ───────────────────────────────────────────────── */

    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: ucwords(str_replace('_', ' ', $field));
        if (empty(trim((string)($this->data[$field] ?? '')))) {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field, string $label = 'Email'): static
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' &&
            !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} is not a valid email address.";
        }
        return $this;
    }

    public function min(string $field, int $min, string $label = ''): static
    {
        $label = $label ?: ucwords(str_replace('_', ' ', $field));
        $val   = $this->data[$field] ?? '';
        if (strlen((string)$val) < $min) {
            $this->errors[$field] = "{$label} must be at least {$min} characters.";
        }
        return $this;
    }

    public function max(string $field, int $max, string $label = ''): static
    {
        $label = $label ?: ucwords(str_replace('_', ' ', $field));
        $val   = $this->data[$field] ?? '';
        if (strlen((string)$val) > $max) {
            $this->errors[$field] = "{$label} must be at most {$max} characters.";
        }
        return $this;
    }

    public function matches(string $field, string $otherField, string $label = ''): static
    {
        $label = $label ?: ucwords(str_replace('_', ' ', $field));
        if (($this->data[$field] ?? '') !== ($this->data[$otherField] ?? '')) {
            $this->errors[$field] = "{$label} does not match.";
        }
        return $this;
    }

    public function numeric(string $field, string $label = ''): static
    {
        $label = $label ?: ucwords(str_replace('_', ' ', $field));
        if (isset($this->data[$field]) && $this->data[$field] !== '' &&
            !is_numeric($this->data[$field])) {
            $this->errors[$field] = "{$label} must be a number.";
        }
        return $this;
    }

    public function date(string $field, string $label = ''): static
    {
        $label = $label ?: ucwords(str_replace('_', ' ', $field));
        $val   = $this->data[$field] ?? '';
        if ($val && !strtotime($val)) {
            $this->errors[$field] = "{$label} is not a valid date.";
        }
        return $this;
    }

    public function dateAfter(string $field, string $afterField, string $label = ''): static
    {
        $label = $label ?: ucwords(str_replace('_', ' ', $field));
        $d1    = strtotime($this->data[$field]     ?? '');
        $d2    = strtotime($this->data[$afterField] ?? '');
        if ($d1 && $d2 && $d1 <= $d2) {
            $this->errors[$field] = "{$label} must be after " . ucwords(str_replace('_',' ',$afterField)) . ".";
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label = ''): static
    {
        $label = $label ?: ucwords(str_replace('_', ' ', $field));
        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = "{$label} contains an invalid value.";
        }
        return $this;
    }

    /* ── Results ─────────────────────────────────────────────── */

    public function passes(): bool { return empty($this->errors); }
    public function fails():  bool { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function firstError(): string { return reset($this->errors) ?: ''; }

    public function sanitize(string $field): string
    {
        return htmlspecialchars(trim((string)($this->data[$field] ?? '')), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeInput(array $data): array
    {
        return array_map(fn($v) => is_string($v) ? trim($v) : $v, $data);
    }
}
