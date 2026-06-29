<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FlexyBundle\DTO;

class ProductPriceDTO
{
    public string $currency = '';
    public float $price = 0.0;
    public ?float $promoPrice = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();

        if (is_string($data['currency'])) {
            $dto->currency = $data['currency'];
        } elseif (is_array($data['currency'])) {
            $dto->currency = $data['currency']['code'] ?? '';
        }

        $dto->price = isset($data['price']) ? (float) $data['price'] : 0.0;
        $dto->promoPrice = array_key_exists('promoPrice', $data) ? (float) $data['promoPrice'] : null;

        return $dto;
    }
}
