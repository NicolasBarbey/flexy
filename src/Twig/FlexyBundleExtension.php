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

use FlexyBundle\Service\ProductSaleElementsService;
use Symfony\Contracts\Translation\TranslatorInterface;
use Thelia\Core\Security\SecurityContext;
use Thelia\Model\Customer;
use Thelia\Model\ProductSaleElements;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlexyBundleExtension extends AbstractExtension
{
    public function __construct(
        private ProductSaleElementsService $pseService,
        private SecurityContext $securityContext, private readonly TranslatorInterface $translator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('attributeAv', [$this, 'attributeAv']),
            new TwigFunction('getCurrentCustomer', [$this, 'getCurrentCustomer']),
            new TwigFunction('getWeekDays', [$this, 'getWeekDays']),
        ];
    }

    public function getCurrentCustomer(): ?Customer
    {
        return $this->securityContext->getCustomerUser();
    }

    public function attributeAv(?ProductSaleElements $pse): array
    {
        if (null === $pse) {
            return [];
        }

        return $this->pseService->getAttributesAvFromPse($pse);
    }

    public function getWeekDays(): array
    {
        return [
            $this->translator->trans('Monday'),
            $this->translator->trans('Tuesday'),
            $this->translator->trans('Wednesday'),
            $this->translator->trans('Thursday'),
            $this->translator->trans('Friday'),
            $this->translator->trans('Saturday'),
            $this->translator->trans('Sunday'),
        ];
    }
}
