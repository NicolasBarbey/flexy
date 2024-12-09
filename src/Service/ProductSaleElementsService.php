<?php

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

use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\AttributeCombination;
use Thelia\Model\ProductSaleElements;

class ProductSaleElementsService
{
    protected ?string $locale = null;

    public function __construct(private readonly RequestStack $requestStack)
    {
        $request = $this->requestStack->getCurrentRequest();

        /** @var Session $session */
        $session = $request->getSession();

        $this->locale = $session->getLang()->getLocale();
    }

    public function getAttributesAvFromPse(ProductSaleElements $pse): array
    {
        $combinations = $pse->getAttributeCombinations();
        $attributesAv = [];
        /** @var AttributeCombination $combination */
        foreach ($combinations as $combination) {
            $title = $combination->getAttribute()->setLocale($this->locale)->getTitle();
            $av = $combination->getAttributeAv()->setLocale($this->locale)->getTitle();

            $attributesAv[$title] = $av;
        }

        return $attributesAv;
    }
}
