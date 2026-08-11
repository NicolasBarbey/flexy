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

namespace FlexyBundle\Service;

use Symfony\Contracts\Service\ResetInterface;
use Thelia\Api\Service\DataAccess\DataAccessService;

/**
 * The product payload carries no image, so every card would fetch its own. Listings
 * call preload() once with the whole page, turning N calls into one.
 *
 * Deliberately not readonly: the resolved map is the point of this service. Under
 * php-fpm Symfony builds it per request, so the map never outlives one page render;
 * ResetInterface makes that hold under a worker runtime too.
 */
final class ProductImageResolver implements ResetInterface
{
    /**
     * Page size is derived from the number of products rather than fixed, so a large
     * listing cannot silently drop rows past a hard ceiling. Rows come back ordered by
     * position across the whole set, so the low positions this map needs come first.
     */
    private const IMAGE_ROWS_PER_PRODUCT = 30;

    /** @var array<int, int|null> */
    private array $imageIdByProduct = [];

    public function __construct(
        private readonly DataAccessService $dataAccessService,
    ) {
    }

    /**
     * @param array<int, int> $productIds
     */
    public function preload(array $productIds): void
    {
        $missing = array_values(array_diff(
            array_unique(array_filter($productIds)),
            array_keys($this->imageIdByProduct),
        ));

        if ([] === $missing) {
            return;
        }

        // Seed every requested id so a product without image is not re-queried.
        foreach ($missing as $productId) {
            $this->imageIdByProduct[$productId] = null;
        }

        // `visible` is not implicit on the front API, and getImages() renders visible
        // images only: without this the first row could be a hidden image, which would
        // win the slot here and then render as the placeholder.
        $rows = $this->dataAccessService->resources('/api/front/product_images', [
            'product.id' => $missing,
            'visible' => true,
            'order[position]' => 'asc',
            'itemsPerPage' => \count($missing) * self::IMAGE_ROWS_PER_PRODUCT,
        ]) ?? [];

        foreach ($rows as $row) {
            $productId = self::extractId($row['product'] ?? null);

            // Ordered by position, so the first row seen for a product is the right one.
            if (null !== $productId && null === ($this->imageIdByProduct[$productId] ?? null)) {
                $this->imageIdByProduct[$productId] = (int) $row['id'];
            }
        }
    }

    public function firstImageId(int $productId): ?int
    {
        if (!\array_key_exists($productId, $this->imageIdByProduct)) {
            $this->preload([$productId]);
        }

        return $this->imageIdByProduct[$productId];
    }

    public function reset(): void
    {
        $this->imageIdByProduct = [];
    }

    /**
     * The relation comes back either embedded or as an IRI, depending on the group.
     */
    private static function extractId(mixed $relation): ?int
    {
        if (\is_array($relation)) {
            return isset($relation['id']) ? (int) $relation['id'] : null;
        }

        if (\is_string($relation) && preg_match('#/(\d+)$#', $relation, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
