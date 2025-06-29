<?php

namespace App\ValueObjects;

class Money
{
    public function __construct(
        public readonly float $value,
        public readonly string $currency = 'GBP'
    ) {}

    public static function fromCents(int $cents, string $currency = 'GBP'): self
    {
        return new self($cents / 100, $currency);
    }

    public function toCents(): int
    {
        return (int) round($this->value * 100);
    }

    public function format(): string
    {
        return '$'.number_format($this->value, 2);
    }

    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot add money with different currencies');
        }

        return new self($this->value + $other->value, $this->currency);
    }

    public function subtract(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot subtract money with different currencies');
        }

        return new self($this->value - $other->value, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        return new self($this->value * $multiplier, $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->value === $other->value && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
