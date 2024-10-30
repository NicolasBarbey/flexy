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

namespace FlexyBundle\Twig\Organisms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use TwigEngine\Service\DataAccess\DataAccessService;

#[AsTwigComponent(template: '@components/Organisms/ProductCard/PurchaseFunnel.html.twig')]
class PurchaseFunnel
{
    public ?string $cartItemId = '';

    public function __construct(private DataAccessService $dataAccessService)
    {
    }

    public function getProduct(): void
    {
    }
}
