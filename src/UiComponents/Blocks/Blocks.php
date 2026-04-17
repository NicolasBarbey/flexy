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

namespace FlexyBundle\UiComponents\Blocks;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Core\Content\BlockRendererInterface;
use Thelia\Core\HttpFoundation\Session\Session;

#[AsTwigComponent(name: 'Flexy:Blocks', template: '@UiComponents/Blocks/Blocks.html.twig')]
class Blocks
{
    #[ExposeInTemplate]
    public ?string $id = null;

    #[ExposeInTemplate]
    public ?string $slug = null;

    #[ExposeInTemplate]
    public ?string $item_type = null;

    #[ExposeInTemplate]
    public ?string $item_id = null;

    #[ExposeInTemplate]
    public ?int $visible = 1;

    #[ExposeInTemplate]
    public ?array $blocks = null;

    public function __construct(
        private readonly DataAccessService $dataAccessService,
        private readonly BlockRendererInterface $blockRenderer,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getBlocks(): array
    {
        $session = $this->requestStack->getCurrentRequest()?->getSession();
        $locale = $session instanceof Session ? $session->getLang()?->getLocale() : null;

        $this->blocks = $this->blockRenderer->findAndRenderBlocks([
            'id' => $this->id,
            'slug' => $this->slug,
            'item_type' => $this->item_type,
            'item_id' => $this->item_id,
            'visible' => $this->visible,
            'locale' => $locale,
        ]);

        return $this->blocks;
    }
}
