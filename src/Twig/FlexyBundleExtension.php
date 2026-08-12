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
use Thelia\Domain\Localization\Service\LangService;
use Thelia\Model\Customer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlexyBundleExtension extends AbstractExtension
{
    public function __construct(
        private readonly SecurityContext $securityContext,
        private readonly LangService $langService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCurrentCustomer', [$this, 'getCurrentCustomer']),
            new TwigFunction('current_locale', [$this, 'currentLocale']),
        ];
    }

    public function getCurrentCustomer(): ?Customer
    {
        return $this->securityContext->getCustomerUser();
    }

    /**
     * Current request locale in the language_TERRITORY form (fr_FR), which is what og:locale
     * expects — unlike lang_code, which carries the two-letter code alone.
     */
    public function currentLocale(): string
    {
        return $this->langService->getLocale() ?: 'en_US';
    }
}
