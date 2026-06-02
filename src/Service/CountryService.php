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

use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;

final readonly class CountryService
{
    public function __construct(
        private Session $session,
    ) {
    }

    /**
     * Récupère le nom traduit d'un pays.
     *
     * @param string|null $locale locale forcée (ex. "fr_FR"), sinon la locale courante de la session
     */
    public function getCountryName(int $countryId, ?string $locale = null): ?string
    {
        $country = $this->getCountry($countryId);

        if (!$country instanceof Country) {
            return null;
        }

        return $country->setLocale($locale ?? $this->getCurrentLocale())->getTitle();
    }

    private function getCountry(int $countryId): ?Country
    {
        return CountryQuery::create()->findPk($countryId);
    }

    private function getCurrentLocale(): string
    {
        return $this->session->getLang()?->getLocale() ?? 'en_US';
    }
}
