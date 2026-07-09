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

namespace FlexyBundle\Components\Layouts\ProductCategory;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent]
class Base
{
    public array $categories = [];

    public function __construct(
        private readonly DataAccessService $dataAccessService,
    ) {
    }

    public function mount(): void
    {
        $categories = $this->dataAccessService->resources('/api/front/categories', [
            'itemsPerPage' => 3,
            'parent' => 0,
            'visible' => true,
        ]) ?? [];

        $this->categories = array_map(
            static fn (array $category): array => [
                'id' => $category['id'],
                'title' => $category['i18ns']['title'] ?? '',
                'href' => $category['publicUrl'] ?? '',
            ],
            $categories,
        );
    }
}
