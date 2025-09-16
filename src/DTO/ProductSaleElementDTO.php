<?php

namespace FlexyBundle\DTO;

class ProductSaleElementDTO
{
    public int $id = 0;
    public string $ref = '';
    public int $quantity = 0;
    public bool $promo = false;
    public bool $newness = false;
    public float $weight = 0.0;
    public bool $isDefault = false;

    /** @var ProductPriceDTO[] */
    public array $productPrices = [];

    public static function fromArray(array $data): self
    {
        $e = new self();
        $e->id = isset($data['id']) ? (int) $data['id'] : 0;
        $e->ref = isset($data['ref']) ? (string) $data['ref'] : '';
        $e->quantity = isset($data['quantity']) ? (int) $data['quantity'] : 0;
        $e->promo = (bool)($data['promo'] ?? false);
        $e->newness = (bool)($data['newness'] ?? false);
        $e->weight = isset($data['weight']) ? (float) $data['weight'] : 0.0;
        $e->isDefault = (bool)($data['isDefault'] ?? false);

        $e->productPrices = array_values(
            array_map(
                static fn(array $row) => ProductPriceDTO::fromArray($row),
                (array)($data['productPrices'] ?? [])
            )
        );

        return $e;
    }
}
