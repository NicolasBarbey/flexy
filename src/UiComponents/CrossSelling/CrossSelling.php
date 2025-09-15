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

namespace FlexyBundle\UiComponents\CrossSelling;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent(name: 'Flexy:CrossSelling', template: '@UiComponents/CrossSelling/CrossSelling.html.twig')]
class CrossSelling
{
    public string $categoryId;
    public array $productIdsToIgnore = [];
    private DataAccessService $dataAccessService;

    #[ExposeInTemplate]
    private array $products;

    public function __construct(DataAccessService $dataAccessService, private TranslatorInterface $translator)
    {
        $this->dataAccessService = $dataAccessService;
    }

    public function getProducts(): array
    {
        $this->products = $this->dataAccessService->resources('/api/front/products', [
            'productCategories.category.id' => $this->categoryId,
            'itemsPerPage' => 3,
            'not_in[id]' => $this->productIdsToIgnore,
        ]);

        return $this->products;
    }
}
