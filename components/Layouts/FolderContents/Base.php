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

namespace FlexyBundle\Components\Layouts\FolderContents;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent]
class Base
{
    private const ITEMS_PER_PAGE = 12;

    public array $contents = [];
    public array $pagination = [];

    public function __construct(
        private readonly DataAccessService $dataAccessService,
    ) {
    }

    public function mount(int $folderId, int $page = 1): void
    {
        $page = max(1, $page);
        $response = $this->fetchPage($folderId, $page);
        $totalItems = (int) ($response['hydra:totalItems'] ?? 0);
        $lastPage = max(1, (int) ceil($totalItems / self::ITEMS_PER_PAGE));

        // Out of range the API serves the last page anyway; realigning keeps the pager from
        // offering a "next" that leads nowhere.
        if ($page > $lastPage) {
            $page = $lastPage;
            $response = $this->fetchPage($folderId, $page);
        }

        $this->contents = $response['hydra:member'] ?? [];
        $this->pagination = [
            'totalItems' => $totalItems,
            'itemsPerPage' => self::ITEMS_PER_PAGE,
            'currentPage' => $page,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPage(int $folderId, int $page): array
    {
        $response = $this->dataAccessService->resources('/api/front/contents', [
            'contentFolders.folder.id' => $folderId,
            'visible' => true,
            'itemsPerPage' => self::ITEMS_PER_PAGE,
            'page' => $page,
        ], 'jsonld');

        return \is_array($response) ? $response : [];
    }
}
