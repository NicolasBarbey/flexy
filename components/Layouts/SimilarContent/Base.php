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

namespace FlexyBundle\Components\Layouts\SimilarContent;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent]
class Base
{
    public int|string|null $folderId = null;
    public int $itemsPerPage = 3;

    public array $contents = [];

    public function __construct(
        private readonly DataAccessService $dataAccessService,
    ) {
    }

    public function mount(): void
    {
        $params = [
            'itemsPerPage' => $this->itemsPerPage,
            'visible' => true,
        ];

        if ($this->folderId !== null) {
            $params['contentFolders.folder.id'] = $this->folderId;
        }

        $contents = $this->dataAccessService->resources('/api/front/contents', $params) ?? [];

        $this->contents = array_map(
            static fn (array $content): array => [
                'id' => $content['id'],
                'title' => $content['i18ns']['title'] ?? '',
                'date' => $content['createdAt'] ?? null,
                'url' => $content['publicUrl'] ?? '',
            ],
            $contents,
        );
    }
}
