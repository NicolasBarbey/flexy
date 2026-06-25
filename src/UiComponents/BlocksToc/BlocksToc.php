<?php

declare(strict_types=1);

namespace FlexyBundle\UiComponents\BlocksToc;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Thelia\Core\HttpFoundation\Session\Session;
use TheliaBlocks\Model\BlockGroupQuery;

#[AsTwigComponent(name: 'Flexy:BlocksToc', template: '@UiComponents/BlocksToc/BlocksToc.html.twig')]
class BlocksToc
{
    public ?string $item_type = null;

    public ?string $item_id = null;

    /** When set, only blockTitle blocks with a level ≤ this value are included. Neutral titles (no level) are excluded when a max level is set. */
    public ?int $max_level = null;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    #[ExposeInTemplate]
    public function getTitles(): array
    {
        $session = $this->requestStack->getCurrentRequest()?->getSession();
        $locale = $session instanceof Session ? $session->getLang()?->getLocale() : null;

        $search = BlockGroupQuery::create()
            ->filterByVisible(1)
            ->useItemBlockGroupQuery()
                ->filterByItemType($this->item_type)
                ->filterByItemId((int) $this->item_id)
            ->endUse();

        $titles = [];
        foreach ($search->find() as $blockGroup) {
            if ($locale !== null) {
                $blockGroup->setLocale($locale);
            }

            try {
                $blocks = json_decode($blockGroup->getJsonContent(), true, 512, \JSON_THROW_ON_ERROR);
                if (!\is_array($blocks)) {
                    continue;
                }
                foreach ($blocks as $block) {
                    if (
                        !isset($block['id'], $block['type']['id'], $block['data']['text'])
                        || $block['type']['id'] !== 'blockTitle'
                        || $block['data']['text'] === ''
                    ) {
                        continue;
                    }

                    $level = isset($block['data']['level']) ? (int) $block['data']['level'] : null;

                    if ($this->max_level !== null && ($level === null || $level > $this->max_level)) {
                        continue;
                    }

                    $titles[] = [
                        'id' => $block['id'],
                        'text' => strip_tags((string) $block['data']['text']),
                    ];
                }
            } catch (\JsonException) {
                // skip malformed block group
            }
        }

        return $titles;
    }
}
