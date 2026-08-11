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

use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Contracts\Service\ResetInterface;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Model\TaxRule;

/**
 * The front API exposes untaxed prices only, so the taxed one has to be computed from
 * the Propel model. Listings preload the whole page at once: alone, each card costs a
 * product query plus a lazy load of its tax rule.
 *
 * Deliberately not readonly: the resolved models are the point of this service. Under
 * php-fpm Symfony builds it per request, so the map never outlives one page render;
 * ResetInterface makes that hold under a worker runtime too.
 */
final class ProductTaxationResolver implements ResetInterface
{
    /** @var array<int, Product|null> */
    private array $productById = [];

    public function __construct(
        private readonly TaxEngine $taxEngine,
    ) {
    }

    /**
     * @param array<int, int> $productIds
     */
    public function preload(array $productIds): void
    {
        $missing = array_values(array_diff(
            array_unique(array_filter($productIds)),
            array_keys($this->productById),
        ));

        if ([] === $missing) {
            return;
        }

        // Seed every requested id so a product without model is not re-queried.
        foreach ($missing as $productId) {
            $this->productById[$productId] = null;
        }

        // joinWith, not a plain join: the tax rule is what the calculator reads, and
        // leaving it unhydrated costs one lazy load per card. LEFT, not the default
        // INNER: `product.tax_rule_id` is nullable, and an inner join would drop those
        // products from the map entirely instead of letting taxedPrice() handle them.
        $products = ProductQuery::create()
            ->joinWithTaxRule(Criteria::LEFT_JOIN)
            ->filterById($missing)
            ->find()
        ;

        foreach ($products as $product) {
            $this->productById[$product->getId()] = $product;
        }
    }

    public function taxedPrice(int $productId, ?float $price): ?float
    {
        if (null === $price) {
            return null;
        }

        $product = $this->product($productId);

        // A product whose model is gone cannot be priced at all. Returning null rather
        // than a wrong figure keeps the caller free to decide what to show.
        if (!$product instanceof Product) {
            return null;
        }

        // No tax rule means no tax applies, so the taxed price is the untaxed one. The
        // calculator types its argument as TaxRule and would fatal on null; templates
        // format the result as currency, where null would print as 0.
        if (!$product->getTaxRule() instanceof TaxRule) {
            return $price;
        }

        return $product->getTaxedPrice($this->taxEngine->getDeliveryCountry(), $price);
    }

    private function product(int $productId): ?Product
    {
        if (!\array_key_exists($productId, $this->productById)) {
            $this->preload([$productId]);
        }

        return $this->productById[$productId];
    }

    public function reset(): void
    {
        $this->productById = [];
    }
}
