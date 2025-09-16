<?php

namespace FlexyBundle\DTO;

class ProductPriceDTO
{
    public string $currency = '';
    public float $price = 0.0;
    public ?float $promoPrice = null;

    public static function fromArray(array $data): self
    {
        $productPriceDTO = new self();

        if (is_string($data['currency'])) {
            $productPriceDTO->currency = $data['currency'];
        } elseif(is_array($data['currency'])) {
            $productPriceDTO->currency = $data['currency']['code'] ?? '';
        }

        $productPriceDTO->price = isset($data['price']) ? (float) $data['price'] : 0.0;
        $productPriceDTO->promoPrice = array_key_exists('promoPrice', $data) ? (float) $data['promoPrice'] : null;

        return $productPriceDTO;
    }
}
