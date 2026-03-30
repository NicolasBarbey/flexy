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

class ProductDTO
{
    public int $id = 0;
    public string $ref = '';
    public bool $visible = false;
    public int $position = 0;
    public bool $virtual = false;
    public string $title = '';
    public string $chapo = '';
    public string $description = '';
    public string $postscriptum = '';
    public array $colors = [];

    /** @var string[] */
    public array $productCategories = [];

    /** @var ProductSaleElementDTO[] */
    public array $productSaleElements = [];

    public ?string $publicUrl = null;

    public static function fromArray(array $data): self
    {
        $productDTO = new self();
        $productDTO->id = isset($data['id']) ? (int) $data['id'] : 0;
        $productDTO->ref = isset($data['ref']) ? (string) $data['ref'] : '';
        $productDTO->visible = (bool) ($data['visible'] ?? false);
        $productDTO->position = isset($data['position']) ? (int) $data['position'] : 0;
        $productDTO->virtual = (bool) ($data['virtual'] ?? false);
        $productDTO->colors = isset($data['ProductColor']['colors']) ? (array) $data['ProductColor']['colors'] : [];

        $productCategories = $data['productCategories'] ?? [];

        $productDTO->productCategories = [];
        if (\is_array($productCategories)) {
            foreach ($productCategories as $entry) {
                if (!\is_array($entry)) {
                    continue;
                }
                $categoryPayload = (isset($entry['category']) && \is_array($entry['category']))
                    ? $entry['category']
                    : $entry;

                $productDTO->productCategories[] = CategoryDTO::fromArray($categoryPayload);
            }
        }

        $productDTO->productSaleElements = array_values(
            array_map(
                static fn (array $row) => ProductSaleElementDTO::fromArray($row),
                (array) ($data['productSaleElements'] ?? [])
            )
        );

        $productDTO->title = isset($data['i18ns']['title']) ? (string) $data['i18ns']['title'] : '';
        $productDTO->chapo = isset($data['i18ns']['chapo']) ? (string) $data['i18ns']['chapo'] : '';
        $productDTO->description = isset($data['i18ns']['description']) ? (string) $data['i18ns']['description'] : '';
        $productDTO->postscriptum = isset($data['i18ns']['postscriptum']) ? (string) $data['i18ns']['postscriptum'] : '';

        $productDTO->publicUrl = isset($data['publicUrl']) ? (string) $data['publicUrl'] : null;

        return $productDTO;
    }

    public static function fromCollection(array $items): array
    {
        return array_values(
            array_map(
                static fn (array $item) => self::fromArray($item),
                $items
            )
        );
    }
}
