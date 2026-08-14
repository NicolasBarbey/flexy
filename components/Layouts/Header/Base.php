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

namespace FlexyBundle\Components\Layouts\Header;

use FlexyBundle\Service\NavigationTree;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent]
class Base
{
    public array $menuItems = [];

    public function __construct(
        private readonly DataAccessService $dataAccessService,
        private readonly NavigationTree $navigationTree,
    ) {
    }

    public function mount(): void
    {
        $categories = $this->navigationTree->categoryRoots();

        $folder = $this->dataAccessService->resources('/api/front/folders/2');
        $content = $this->dataAccessService->resources('/api/front/contents/1');

        $this->menuItems = array_map(
            static fn (array $category): array => [
                'type' => 'category',
                'id' => $category['id'],
                'title' => $category['title'],
                'href' => $category['href'],
            ],
            $categories,
        );

        if ($folder !== null) {
            $this->menuItems[] = [
                'type' => 'folder',
                'id' => $folder['id'],
                'title' => $folder['i18ns']['title'] ?? '',
                'href' => $folder['publicUrl'] ?? '',
                'includeContents' => true,
            ];
        }

        if ($content !== null) {
            $this->menuItems[] = [
                'type' => 'link',
                'id' => $content['id'],
                'title' => $content['i18ns']['title'] ?? '',
                'href' => $content['publicUrl'] ?? '',
            ];
        }
    }
}
