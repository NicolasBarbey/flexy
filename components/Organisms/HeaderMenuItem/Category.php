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
class Category extends AbstractHeaderMenuItem
{
    public int|string|null $id = null;
    public string $title = '';
    public string $href = '';
    public array $columns = [];
    public array $leafLinks = [];
    public bool $showSeeMore = false;

    public function __construct(DataAccessService $dataAccessService)
    {
        parent::__construct($dataAccessService);
    }

    public function mount(int|string|null $id = null, ?string $title = null, ?string $href = null): void
    {
        $this->id = $id;

        [$this->title, $this->href] = $this->resolveTitleAndHref('/api/front/categories', $id, $title, $href);

        if ($id === null) {
            return;
        }

        $branches = $this->dataAccessService->resources('/api/front/categories', ['parent' => $id]) ?? [];

        $result = $this->buildMegaMenu(
            $branches,
            fn (int|string $branchId): array => $this->dataAccessService->resources(
                '/api/front/categories',
                ['parent' => $branchId, 'visible' => true],
            ) ?? [],
        );

        $this->columns = $result['columns'];
        $this->leafLinks = $result['leafLinks'];
    }
}
