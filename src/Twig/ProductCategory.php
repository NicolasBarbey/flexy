<?php

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

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use TwigEngine\Service\DataAccess\DataAccessService;
use TheliaLibrary\Service\ImageService;

#[AsTwigComponent(template: 'components/Layout/ProductCategory/ProductCategory.html.twig')]
class ProductCategory
{
    public string $categoryId;

    public function __construct(private DataAccessService $dataAccessService, private TranslatorInterface $translator, private ImageService $imageService)
    {
    }

    public function getCategories(): array
    {
        $categories = $this->dataAccessService->resources('/api/front/categories', [
            'itemsPerPage' => 3,
        ]);
       
        return array_map(function ($item) {
            $params = [
                'source_type' => 'category',
                'source_id' => $item['id'],
                'filters'=> 'default',
                'position' => 1
                
            ];
            $images = $this->imageService->getImages($params);

     
           
            return [
                'title' => $item['i18ns']['title'],
                'button' => [
                    'label' => $this->translator->trans('Discover'),
                    'href' => $item['publicUrl'],
                ],
                'img' => [
                    'url' => $images[0]['sources'][0]['url'] ?? null,
                    'alt' => $images[0]['data']['title'] ?? $item['i18ns']['title'],
                ],
                'url' => $item['publicUrl'],
            ];
        }, $categories);
    }
}
