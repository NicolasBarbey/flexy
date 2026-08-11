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

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent]
class Folder extends AbstractHeaderMenuItem
{
    public int|string|null $id = null;
    public string $menuKey = '';
    public string $title = '';
    public string $href = '';
    public bool $includeContents = false;
    public array $columns = [];
    public array $leafLinks = [];
    public bool $showSeeMore = false;

    public function __construct(DataAccessService $dataAccessService)
    {
        parent::__construct($dataAccessService);
    }

    public function mount(int|string|null $id = null, ?string $title = null, ?string $href = null, bool $includeContents = false): void
    {
        $this->id = $id;
        $this->menuKey = $this->buildMenuKey($id);
        $this->includeContents = $includeContents;

        [$this->title, $this->href] = $this->resolveTitleAndHref('/api/front/folders', $id, $title, $href);

        if ($id === null) {
            return;
        }

        $branches = $this->dataAccessService->resources(
            '/api/front/folders',
            ['parent' => $id, 'visible' => true],
        ) ?? [];
        $extraLeaves = $includeContents
            ? ($this->dataAccessService->resources('/api/front/contents', ['contentFolders.folder.id' => $id, 'visible' => true]) ?? [])
            : [];

        $result = $this->buildMegaMenu(
            $branches,
            function (int|string $branchId) use ($includeContents): array {
                $children = $this->dataAccessService->resources(
                    '/api/front/folders',
                    ['parent' => $branchId, 'visible' => true],
                ) ?? [];

                if ($includeContents) {
                    $children = array_merge(
                        $children,
                        $this->dataAccessService->resources(
                            '/api/front/contents',
                            ['contentFolders.folder.id' => $branchId, 'visible' => true],
                        ) ?? [],
                    );
                }

                return $children;
            },
            $extraLeaves,
        );

        $this->columns = $result['columns'];
        $this->leafLinks = $result['leafLinks'];
    }
}
