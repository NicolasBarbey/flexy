<?php

namespace FlexyBundle\DTO;

class ProductPriceDTO
{
    public string $currency = '';
    public float $price = 0.0;
    public ?float $promoPrice = null;

    public static function fromArray(array $data): self
    {
        $pp = new self();
        $pp->currency = isset($data['currency']) ? (string) $data['currency'] : '';
        $pp->price = isset($data['price']) ? (float) $data['price'] : 0.0;
        $pp->promoPrice = array_key_exists('promoPrice', $data) ? (float) $data['promoPrice'] : null;

        return $pp;
    }
}
