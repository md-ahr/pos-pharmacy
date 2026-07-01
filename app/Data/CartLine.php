<?php

namespace App\Data;

readonly class CartLine
{
    public function __construct(
        public int $productId,
        public ?int $productUnitId,
        public int $quantity,
        public ?int $batchId = null,
        public string $lineDiscount = '0.00',
        public bool $isPrescriptionItem = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            productUnitId: isset($data['product_unit_id']) && $data['product_unit_id'] !== null
                ? (int) $data['product_unit_id']
                : null,
            quantity: (int) $data['quantity'],
            batchId: isset($data['batch_id']) && $data['batch_id'] !== null
                ? (int) $data['batch_id']
                : null,
            lineDiscount: (string) ($data['line_discount'] ?? '0.00'),
            isPrescriptionItem: (bool) ($data['is_prescription_item'] ?? false),
        );
    }
}
