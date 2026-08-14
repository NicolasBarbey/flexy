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

namespace FlexyBundle\Components\Organisms\LangSelect;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Core\HttpFoundation\Session\Session;

#[AsTwigComponent]
class Base
{
    public function __construct(
        private readonly DataAccessService $dataAccessService,
        private readonly Session $session,
    ) {
    }

    public function getLangs(): array
    {
        return $this->dataAccessService->resources('/api/front/languages', [
            'active' => true,
        ]) ?? [];
    }

    public function getCurrentLang(): ?array
    {
        $langs = $this->getLangs();

        if (\count($langs) <= 1) {
            return null;
        }

        $currentId = $this->session->getLang()?->getId();

        foreach ($langs as $lang) {
            if ((int) $lang['id'] === $currentId) {
                return $lang;
            }
        }

        return null;
    }
}
