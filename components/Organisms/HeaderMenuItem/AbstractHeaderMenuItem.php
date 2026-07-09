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

namespace FlexyBundle\Components\Organisms\HeaderMenuItem;

use Thelia\Api\Service\DataAccess\DataAccessService;

abstract class AbstractHeaderMenuItem
{
    public function __construct(
        protected readonly DataAccessService $dataAccessService,
    ) {
    }

    /**
     * Resolves title/publicUrl via resources() only if not already provided.
     *
     * @return array{0: string, 1: string} [title, href]
     */
    protected function resolveTitleAndHref(string $endpoint, int|string|null $id, ?string $title, ?string $href): array
    {
        if ($title !== null && $href !== null) {
            return [$title, $href];
        }

        if ($id === null) {
            return [$title ?? '', $href ?? ''];
        }

        $entity = $this->dataAccessService->resources($endpoint.'/'.$id);

        return [
            $title ?? ($entity['i18ns']['title'] ?? ''),
            $href ?? ($entity['publicUrl'] ?? ''),
        ];
    }

    /**
     * Unique navigation key for the mobile drill-down (data-menu-sub) — prefixed by the
     * concrete class name, since Category and Folder ids live in separate spaces and can collide.
     */
    protected function buildMenuKey(int|string|null $id): string
    {
        $parts = explode('\\', static::class);

        return strtolower(end($parts)).'-'.$id;
    }

    /**
     * Groups branches with children into columns, others into leafLinks.
     *
     * @param array<int, array<string, mixed>>                       $branches
     * @param callable(int|string): array<int, array<string, mixed>> $fetchChildren
     * @param array<int, array<string, mixed>>                       $extraLeaves
     *
     * @return array{columns: array<int, array{branch: array, children: array}>, leafLinks: array<int, array>}
     */
    protected function buildMegaMenu(array $branches, callable $fetchChildren, array $extraLeaves = []): array
    {
        $columns = [];
        $leafLinks = [];

        foreach ($branches as $branch) {
            $children = $fetchChildren($branch['id']);

            if (\count($children) > 0) {
                $columns[] = ['branch' => $branch, 'children' => $children];
            } else {
                $leafLinks[] = $branch;
            }
        }

        return [
            'columns' => $columns,
            'leafLinks' => array_merge($leafLinks, $extraLeaves),
        ];
    }
}
