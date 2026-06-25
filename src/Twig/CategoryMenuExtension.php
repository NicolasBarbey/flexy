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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Builds the whole visible category tree once per locale and caches it.
 *
 * The navigation menu used to walk the tree with one resources() call per node
 * (~49 calls / ~778 SQL), replayed on every page and rendered twice (desktop
 * header + mobile/searchbar). This assembles the nested tree a single time and
 * serves it from the app cache, so the menu costs one cache read on warm
 * requests instead of hundreds of round-trips to the database.
 *
 * The per-depth resources() parameters mirror exactly what Header /ItemHeader
 * used to send, so the rendered markup stays byte-identical:
 *   depth 0 (Header roots)            -> {parent: 0, visible: true}
 *   depth 1 (ItemHeader subCategories)-> {parent: id}            (no visible filter)
 *   depth 2 (ItemHeader sub2Categories)-> {parent: id, visible: true}
 */
final class CategoryMenuExtension extends AbstractExtension
{
    private const CACHE_TTL = 3600;

    /**
     * Deepest level the menu renders; nodes below this keep an empty children
     * list, so the tree is never walked further than the template reads it.
     */
    private const MAX_DEPTH = 2;

    public function __construct(
        private readonly DataAccessService $dataAccessService,
        private readonly CacheInterface $cache,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('category_menu_tree', [$this, 'categoryMenuTree']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function categoryMenuTree(): array
    {
        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'default';

        return $this->cache->get('flexy_category_menu_tree_'.$locale, function (ItemInterface $item): array {
            $item->expiresAfter(self::CACHE_TTL);

            return $this->buildTree(0, 0);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(int $parentId, int $depth): array
    {
        $params = ['parent' => $parentId];
        // Depth 1 mirrors ItemHeader's subCategories call, which omits the
        // visible filter; every other depth keeps visible: true.
        if (1 !== $depth) {
            $params['visible'] = true;
        }

        $categories = $this->dataAccessService->resources('/api/front/categories', $params) ?? [];

        $tree = [];
        foreach ($categories as $category) {
            $category['children'] = $depth < self::MAX_DEPTH
                ? $this->buildTree((int) $category['id'], $depth + 1)
                : [];
            $tree[] = $category;
        }

        return $tree;
    }
}
