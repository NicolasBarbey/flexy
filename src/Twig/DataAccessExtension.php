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

use Thelia\Api\Service\DataAccess\AttributeAccessService;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DataAccessExtension extends AbstractExtension
{
    public function __construct(
        private readonly DataAccessService $dataAccessService,
        private readonly AttributeAccessService $attributeAccessService,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('resources', [$this, 'resources']),
            new TwigFunction('attr', [$this, 'attribute']),
        ];
    }

    public function resources(string $path, array $params = []): array|object|null
    {
        return $this->dataAccessService->resources($path, $params);
    }

    public function attribute(string $type, string $attributeName): mixed
    {
        $methodName = 'attribute' . ucfirst($type);
        if (!method_exists($this->attributeAccessService, $methodName)) {
            throw new \RuntimeException(sprintf('Method %s not found in %s', $methodName, AttributeAccessService::class));
        }

        return $this->attributeAccessService->$methodName($attributeName);
    }
}
