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

use Symfony\Component\String\Slugger\AsciiSlugger;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SlugIdExtension extends AbstractExtension
{
    private readonly AsciiSlugger $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger('fr');
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('slug_id', $this->slugId(...)),
        ];
    }

    /**
     * Builds a human-readable, URL-safe identifier from a text and an opaque ID.
     *
     * The result combines a truncated ASCII slug of the text with a short prefix
     * of the ID, separated by a dash. This gives both readability and uniqueness
     * without relying solely on the raw ID.
     *
     * Example: slugId('Dératisation Paris', 'zzofpQnG4x', 40) → 'deratisation-paris-zzofpQ'
     *
     * @param string $text      Source text to slugify (HTML tags are stripped first)
     * @param string $id        Opaque unique identifier; only its first $idLength chars are used
     * @param int    $maxLength Maximum length of the slug part (default: 40)
     * @param int    $idLength  Number of characters taken from $id (default: 6)
     */
    public function slugId(string $text, string $id, int $maxLength = 40, int $idLength = 6): string
    {
        $slug = (string) $this->slugger->slug(strip_tags($text))->lower();

        if (mb_strlen($slug) > $maxLength) {
            $slug = rtrim(mb_substr($slug, 0, $maxLength), '-');
        }

        return $slug . '-' . substr($id, 0, $idLength);
    }
}
