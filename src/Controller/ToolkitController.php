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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/toolkit', name: 'toolkit_')]
class ToolkitController extends AbstractController
{
    private const array LABELS = [
        '2xs' => 'Mobile S',
        'xs' => 'Mobile M',
        'sm' => 'Mobile L',
        'md' => 'Tablet',
        'lg' => 'Small Desktop',
        'xl' => 'Desktop',
        '2xl' => 'Large Desktop',
    ];

    #[Route('', name: 'index')]
    public function index(Request $request): Response
    {
        $componentsDir = \dirname(__DIR__, 2) . '/components';
        $partialsDir = \dirname(__DIR__, 2) . '/partials';

        $finder = (new Finder())
            ->files()
            ->name('toolkit.html.twig')
            ->in([$componentsDir, $partialsDir])
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

        if (isset($grouped['Layouts'])) {
            $layouts = $grouped['Layouts'];
            unset($grouped['Layouts']);
            $grouped['Layouts'] = $layouts;
        }

        return $this->render('@Flexy/Toolkit/index.html.twig', [
            'grouped' => $grouped,
            'breakpoints' => $this->getBreakpoints(),
            'embed' => $request->query->getBoolean('embed'),
        ]);
    }

    private function getBreakpoints(): array
    {
        $variablesPath = \dirname(__DIR__, 2) . '/assets/styles/variables.css';
        $css = is_file($variablesPath) ? file_get_contents($variablesPath) : '';

        preg_match_all('/--breakpoint-([\w-]+):\s*([\d.]+)rem/', (string) $css, $matches, \PREG_SET_ORDER);

        $breakpoints = [];
        foreach ($matches as $match) {
            $breakpoints[$match[1]] = (float) $match[2];
        }

        asort($breakpoints);

        return array_map(
            static fn (string $name, float $rem): array => [
                'name' => $name,
                'rem' => $rem,
                'px' => $rem * 16,
                'label' => self::LABELS[$name] ?? null,
            ],
            array_keys($breakpoints),
            array_values($breakpoints),
        );
    }
}
