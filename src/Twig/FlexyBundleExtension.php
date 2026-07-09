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

namespace FlexyBundle\Twig;

use Thelia\Core\Security\SecurityContext;
use Thelia\Model\Customer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlexyBundleExtension extends AbstractExtension
{
    public function __construct(
        private readonly SecurityContext $securityContext,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCurrentCustomer', [$this, 'getCurrentCustomer']),
        ];
    }

    public function getCurrentCustomer(): ?Customer
    {
        return $this->securityContext->getCustomerUser();
    }
}
