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

use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Type\BooleanOrBothType;
use TheliaBlocks\Model\BlockGroupQuery;
use TheliaBlocks\Service\JsonBlockService;
use Thelia\Api\Service\DataAccess\DataAccessService;

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
        private JsonBlockService $jsonBlockService,
        private RequestStack $requestStack,
    ) {
    }

    public function getBlocks()
    {
        $request = $this->requestStack->getCurrentRequest();

        /** @var Session $session */
        $session = $request->getSession();

        $locale = $session->getLang()->getLocale();

        $search = BlockGroupQuery::create();

        if (null !== $this->id) {
            $search->filterById($this->id, Criteria::IN);
        }

        if (null !== $this->slug) {
            $search->filterBySlug($this->slug, Criteria::IN);
        }
        if (null !== $this->item_id && null !== $this->item_type) {
            $search->useItemBlockGroupQuery()
              ->filterByItemType($this->item_type)
              ->filterByItemId($this->item_id)
              ->endUse();
        }

        if ($this->visible !== BooleanOrBothType::ANY) {
            $search->filterByVisible($this->visible ? 1 : 0);
        }
        $blocks = $search->find();

        foreach ($blocks as $block) {
            $this->blocks[] = $this->jsonBlockService->renderJsonBlocks($block->setLocale($locale)->getJsonContent());
        }

        return $this->blocks;
    }
}
