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

use FlexyBundle\Service\NavigationTree;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Template-facing entry point for the navigation tree, kept deliberately thin over
 * NavigationTree — the same relationship DataAccessExtension has with DataAccessService.
 *
 * The theme's own templates do not call it: the header components read the service in PHP,
 * which is where this theme keeps its data access. It exists so that a project overriding a
 * template can reach the tree without reimplementing the traversal, and because the upstream
 * theme exposes the same function under the same name.
 *
 * Nodes come out normalised as {id, title, href, children}.
 */
class NavigationExtension extends AbstractExtension
{
    public function __construct(
        private readonly NavigationTree $navigationTree,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('category_menu_tree', [$this, 'categoryMenuTree']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function categoryMenuTree(): array
    {
        return $this->navigationTree->categoryRoots();
    }
}
