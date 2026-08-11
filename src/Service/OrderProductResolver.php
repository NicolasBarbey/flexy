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

use FlexyBundle\DTO\ProductDTO;
use Thelia\Api\Service\DataAccess\DataAccessService;

/**
 * An OrderProduct resource carries neither the product id nor its public URL: only
 * the sale element reference. Every consumer therefore has to walk back to the
 * product through the sale element, which is what this service centralises.
 */
final readonly class OrderProductResolver
{
    /**
     * Page sizes are derived from the input rather than fixed, so a large order cannot
     * silently drop rows past a hard ceiling. Image queries return one row per image,
     * hence a per-item allowance rather than a plain count.
     */
    private const IMAGE_ROWS_PER_ITEM = 30;

    public function __construct(
        private DataAccessService $dataAccessService,
    ) {
    }

    /**
     * Resolves a whole set of order lines in a fixed number of calls instead of two to
     * three per line. `ref`, `id` and `product.id` are declared search filters and accept
     * arrays, which the Propel bridge turns into an IN clause.
     *
     * @param array<int, array<string, mixed>> $orderProducts
     *
     * @return array<int, array{url: string|null, imageId: int|null, title: string}>
     */
    public function resolveThumbnails(array $orderProducts, ?int $resolveLimit = null): array
    {
        // The caller may hand over a filtered list, whose keys would then not be 0-based:
        // the loop below compares $index to $resolveLimit, so reindex first.
        $orderProducts = array_values($orderProducts);
        $resolved = null === $resolveLimit ? $orderProducts : \array_slice($orderProducts, 0, $resolveLimit);

        [$products, $imageIdByProduct, $imageIdBySaleElement] = $this->fetchBatch($resolved);

        $thumbnails = [];

        foreach ($orderProducts as $index => $line) {
            $product = $products[(string) ($line['productRef'] ?? '')] ?? null;

            // Past the resolve limit the card only needs a placeholder: the CSS collapses
            // those entries into the "+N" badge, so their visual is never displayed.
            $beyondLimit = null !== $resolveLimit && $index >= $resolveLimit;

            // OrderProduct snapshots the label at purchase time, which is the honest one
            // to show; the live product title is only a fallback.
            $title = (string) ($line['title'] ?? '');

            if ('' === $title && null !== $product) {
                $title = $product->title;
            }

            $thumbnails[] = [
                'url' => $beyondLimit
                    ? null
                    : $this->buildProductUrl($product, (string) ($line['productSaleElementsRef'] ?? '')),
                'imageId' => $beyondLimit
                    ? null
                    : ($imageIdBySaleElement[(int) ($line['productSaleElementsId'] ?? 0)]
                        ?? (null === $product ? null : ($imageIdByProduct[$product->id] ?? null))),
                'title' => $title,
            ];
        }

        return $thumbnails;
    }

    /**
     * Everything a set of order lines needs to render, in a fixed number of calls: left
     * to itself each card walks back to its product, its sale element and its image, so
     * the page cost grows with the number of lines.
     *
     * @param array<int, array<string, mixed>> $orderProducts
     *
     * @return array<int, array{orderProduct: array<string, mixed>, product: ProductDTO|null, pse: array<string, mixed>|null, imageId: int|null}>
     */
    public function resolveLines(array $orderProducts): array
    {
        $orderProducts = array_values($orderProducts);

        [$products, $imageIdByProduct, $imageIdBySaleElement] = $this->fetchBatch($orderProducts);
        $saleElements = $this->fetchSaleElementsByRef(self::collect($orderProducts, 'productSaleElementsRef'));

        $lines = [];

        foreach ($orderProducts as $line) {
            $product = $products[(string) ($line['productRef'] ?? '')] ?? null;
            $saleElementId = (int) ($line['productSaleElementsId'] ?? 0);

            $lines[] = [
                'orderProduct' => $line,
                'product' => $product,
                'pse' => $saleElements[$saleElementId] ?? null,
                'imageId' => $imageIdBySaleElement[$saleElementId]
                    ?? (null === $product ? null : ($imageIdByProduct[$product->id] ?? null)),
            ];
        }

        return $lines;
    }

    /**
     * The three lookups both public methods need, in three calls. Kept together on
     * purpose: they are interdependent — the pivot ids are only trustworthy once the
     * visible images are known — and splitting them let a fix land on one caller only.
     *
     * Join on the product reference the order line snapshots: the sale element
     * collection does not expose its `product` relation (only the item endpoint does),
     * so going through it would cost one call per line for nothing.
     *
     * @param array<int, array<string, mixed>> $lines
     *
     * @return array{0: array<string, ProductDTO>, 1: array<int, int>, 2: array<int, int>}
     */
    private function fetchBatch(array $lines): array
    {
        $products = $this->fetchProductsByRef(self::collect($lines, 'productRef'));

        $productIds = array_values(array_map(static fn (ProductDTO $p): int => $p->id, $products));
        [$imageIdByProduct, $visibleImageIds] = $this->fetchVisibleImages($productIds);

        // The pivot resource has no `visible` of its own, so a variant image hidden by
        // the merchant would win over the product's first visible one and then render as
        // the placeholder. Keep only ids the visible query above confirmed.
        $imageIdBySaleElement = array_filter(
            $this->fetchSaleElementImageIds(self::collect($lines, 'productSaleElementsId')),
            static fn (int $imageId): bool => isset($visibleImageIds[$imageId]),
        );

        return [$products, $imageIdByProduct, $imageIdBySaleElement];
    }

    /**
     * `id` is not a declared filter on the sale element resource, `ref` is: query by ref,
     * then index by id, which is what the order lines carry and cannot collide.
     *
     * @param array<int, scalar> $refs
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetchSaleElementsByRef(array $refs): array
    {
        if ([] === $refs) {
            return [];
        }

        $rows = $this->dataAccessService->resources('/api/front/product_sale_elements', [
            'ref' => $refs,
            'itemsPerPage' => \count($refs),
        ]) ?? [];

        $byId = [];
        foreach ($rows as $row) {
            if (isset($row['id'])) {
                $byId[(int) $row['id']] = $row;
            }
        }

        return $byId;
    }

    /**
     * @param array<int, array<string, mixed>> $lines
     *
     * @return array<int, scalar>
     */
    private static function collect(array $lines, string $key): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (array $line): mixed => $line[$key] ?? null,
            $lines,
        ))));
    }

    /**
     * @param array<int, scalar> $productRefs
     *
     * @return array<string, ProductDTO>
     */
    private function fetchProductsByRef(array $productRefs): array
    {
        if ([] === $productRefs) {
            return [];
        }

        $rows = $this->dataAccessService->resources('/api/front/products', [
            'ref' => $productRefs,
            'itemsPerPage' => \count($productRefs),
        ]) ?? [];

        $products = [];
        foreach ($rows as $row) {
            $products[(string) $row['ref']] = ProductDTO::fromArray($row);
        }

        return $products;
    }

    /**
     * @param array<int, int> $saleElementIds
     *
     * @return array<int, int>
     */
    private function fetchSaleElementImageIds(array $saleElementIds): array
    {
        if ([] === $saleElementIds) {
            return [];
        }

        $rows = $this->dataAccessService->resources('/api/front/product_sale_elements_product_image', [
            'productSaleElementsId' => $saleElementIds,
            'itemsPerPage' => \count($saleElementIds) * self::IMAGE_ROWS_PER_ITEM,
        ]) ?? [];

        $imageIds = [];
        foreach ($rows as $row) {
            $saleElementId = isset($row['productSaleElementsId']) ? (int) $row['productSaleElementsId'] : null;

            if (null !== $saleElementId && !isset($imageIds[$saleElementId]) && isset($row['productImageId'])) {
                $imageIds[$saleElementId] = (int) $row['productImageId'];
            }
        }

        return $imageIds;
    }

    /**
     * `visible` is not implicit on the front API. One pass yields both the first visible
     * image of each product and the full set of visible ids, used to vet the pivot above.
     *
     * Rows come back ordered by position across the whole set, so the low positions —
     * the ones the first-image map needs — are never the ones a page size would cut.
     *
     * @param array<int, int> $productIds
     *
     * @return array{0: array<int, int>, 1: array<int, true>}
     */
    private function fetchVisibleImages(array $productIds): array
    {
        if ([] === $productIds) {
            return [[], []];
        }

        $rows = $this->dataAccessService->resources('/api/front/product_images', [
            'product.id' => $productIds,
            'visible' => true,
            'order[position]' => 'asc',
            'itemsPerPage' => \count($productIds) * self::IMAGE_ROWS_PER_ITEM,
        ]) ?? [];

        $imageIds = [];
        $visibleImageIds = [];

        foreach ($rows as $row) {
            $productId = self::extractId($row['product'] ?? null);
            $imageId = (int) $row['id'];
            $visibleImageIds[$imageId] = true;

            // Ordered by position, so the first row seen for a product is the right one.
            if (null !== $productId && !isset($imageIds[$productId])) {
                $imageIds[$productId] = $imageId;
            }
        }

        return [$imageIds, $visibleImageIds];
    }

    /**
     * A relation comes back either embedded (possibly reduced to its id) or as an IRI.
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

    public function resolveProduct(int $saleElementId, int|string|null $productId = null): ?ProductDTO
    {
        $saleElement = $this->dataAccessService->resources('/api/front/product_sale_elements/'.$saleElementId);
        $product = $saleElement['product'] ?? null;

        // The relation never carries publicUrl: ResourceService only decorates the
        // top-level entry, never nested relations. The second call is unavoidable.
        $productId = self::extractId($product) ?? $productId;

        if (null === $productId) {
            return null;
        }

        $data = $this->dataAccessService->resources('/api/front/products/'.$productId);

        return \is_array($data) ? ProductDTO::fromArray($data) : null;
    }

    /**
     * Single-line counterpart of resolveThumbnails(), with the same visibility rule: the
     * pivot carries no `visible`, so its id only wins when the visible query confirms it.
     */
    public function resolveImageId(int $saleElementId, ?int $productId): ?int
    {
        if (null === $productId) {
            return null;
        }

        $visibleImages = $this->dataAccessService->resources('/api/front/product_images', [
            'product.id' => $productId,
            'visible' => true,
            'order[position]' => 'asc',
            'itemsPerPage' => self::IMAGE_ROWS_PER_ITEM,
        ]) ?? [];

        if ([] === $visibleImages) {
            return null;
        }

        $saleElementImages = $this->dataAccessService->resources('/api/front/product_sale_elements_product_image', [
            'productSaleElementsId' => $saleElementId,
            'itemsPerPage' => 1,
        ]) ?? [];

        $saleElementImageId = isset($saleElementImages[0]['productImageId'])
            ? (int) $saleElementImages[0]['productImageId']
            : null;

        foreach ($visibleImages as $image) {
            // The sale element may carry its own visual, which beats the first one.
            if (null !== $saleElementImageId && (int) $image['id'] === $saleElementImageId) {
                return $saleElementImageId;
            }
        }

        return isset($visibleImages[0]['id']) ? (int) $visibleImages[0]['id'] : null;
    }

    /**
     * The product URL is not always rewritten: an untranslated locale falls back to the
     * `?view=product&...` form, which already carries a query string.
     */
    public function buildProductUrl(?ProductDTO $product, string $saleElementRef): ?string
    {
        if (null === $product?->publicUrl) {
            return null;
        }

        if ('' === $saleElementRef) {
            return $product->publicUrl;
        }

        $separator = str_contains($product->publicUrl, '?') ? '&' : '?';

        return $product->publicUrl.$separator.'ref='.rawurlencode($saleElementRef);
    }
}
