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

namespace FlexyBundle\UiComponents\LangSelect;

use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent(name: 'Flexy:LangSelect', template: '@UiComponents/LangSelect/LangSelect.html.twig')]
class LangSelect
{
    use DefaultActionTrait;

    #[ExposeInTemplate()]
    public bool $open = true;

    #[ExposeInTemplate()]
    public ?array $currentLang;

    #[ExposeInTemplate()]
    public array $langs = [];

    public function __construct(private DataAccessService $dataAccessService, private readonly Session $session)
    {
    }

    public function getLangs(): array
    {
        $this->langs = $this->dataAccessService->resources('/api/front/languages', [
            'active' => true,
        ]) ?? [];

        return $this->langs;
    }

    public function getCurrentLang(): ?array
    {
        $langs = $this->getLangs();

        if (\count($langs) <= 1) {
            return null;
        }

        $filters = array_filter($this->getLangs(), fn ($lang) => $lang['id'] == $this->session->getLang()->getId());

        $this->currentLang = reset($filters);

        return $this->currentLang;
    }
}
