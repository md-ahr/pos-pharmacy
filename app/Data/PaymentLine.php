<?php

namespace App\Data;

use App\Enums\PaymentMethod;

readonly class PaymentLine
{
    public function __construct(
        public PaymentMethod $method,
        public string $amount,
        public ?string $reference = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            method: PaymentMethod::from((string) $data['method']),
            amount: (string) $data['amount'],
            reference: isset($data['reference']) ? (string) $data['reference'] : null,
        );
    }
}
