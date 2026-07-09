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

use FlexyBundle\Service\CountryService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CountryExtension extends AbstractExtension
{
    public function __construct(
        private readonly CountryService $countryService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('country_name', [$this, 'countryName']),
        ];
    }

    public function countryName(int $countryId, ?string $locale = null): ?string
    {
        return $this->countryService->getCountryName($countryId, $locale);
    }
}
