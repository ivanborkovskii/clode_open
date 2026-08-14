<?php
/**
 * Валидация форм на сервере.
 * Клиентская проверка — только удобство, решение принимается здесь.
 */

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    /** @var array<string, string> поле => текст ошибки */
    private array $errors = [];

    /** @param array<string, mixed> $input */
    public function __construct(private readonly array $input)
    {
    }

    public function required(string $field, string $message): self
    {
        if ($this->value($field) === '') {
            $this->fail($field, $message);
        }

        return $this;
    }

    public function length(string $field, int $min, int $max, string $message): self
    {
        $length = mb_strlen($this->value($field));

        if ($length > 0 && ($length < $min || $length > $max)) {
            $this->fail($field, $message);
        }

        return $this;
    }

    public function email(string $field, string $message): self
    {
        $value = $this->value($field);

        // Поле необязательное: пустое пропускаем, заполненное — проверяем.
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->fail($field, $message);
        }

        return $this;
    }

    /**
     * Телефон: считаем цифры, а не сверяем формат —
     * пользователь может писать со скобками, пробелами или через +7.
     */
    public function phone(string $field, string $message): self
    {
        $digits = preg_replace('/\D+/', '', $this->value($field)) ?? '';

        if ($digits !== '' && (strlen($digits) < 10 || strlen($digits) > 15)) {
            $this->fail($field, $message);
        }

        return $this;
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function value(string $field): string
    {
        $raw = $this->input[$field] ?? '';

        return is_string($raw) ? trim($raw) : '';
    }

    private function fail(string $field, string $message): void
    {
        // Первая ошибка по полю — самая понятная, последующие не перетираем.
        $this->errors[$field] ??= $message;
    }
}
