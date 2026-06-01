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

namespace FlexyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/toolkit', name: 'toolkit_')]
class ToolkitController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        $componentsDir = \dirname(__DIR__, 2) . '/components';

        $finder = (new Finder())
            ->files()
            ->name('toolkit.html.twig')
            ->in($componentsDir)
            ->sortByName();

        $grouped = [];

        foreach ($finder as $file) {
            $parts = explode('/', $file->getRelativePath());
            $category = $parts[0];
            $name = \count($parts) > 1 ? implode(' / ', \array_slice($parts, 1)) : $category;
            $slug = strtolower(implode('-', $parts));

            $grouped[$category][] = [
                'twigPath' => '@Flexy/' . $file->getRelativePathname(),
                'name' => $name,
                'slug' => $slug,
            ];
        }

        return $this->render('@Flexy/Toolkit/index.html.twig', [
            'grouped' => $grouped,
        ]);
    }
}
