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

namespace FlexyBundle\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Thelia\Core\Template\ParserInterface;
use Thelia\Model\ConfigQuery;

final readonly class SitemapGenerator
{
    public const SITEMAP_CACHE_KEY = 'sitemap';

    public function __construct(
        private AdapterInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function generate(
        ?ParserInterface $parser,
        string $lang,
        string $context,
        bool $flush,
    ): CacheItem {
        $cacheKey = self::SITEMAP_CACHE_KEY.$lang.$context;
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($flush || !$cacheItem->isHit()) {
            $cacheExpire = (int) (ConfigQuery::read('sitemap_ttl', '7200')) ?: 7200;

            $cacheContent = $parser?->render(
                'sitemap',
                [
                    '_lang_' => $lang,
                    '_context_' => $context,
                ],
                false,
            );

            $cacheItem->expiresAfter($cacheExpire);
            $cacheItem->set($cacheContent);
            $this->cache->save($cacheItem);
        }

        return $cacheItem;
    }
}
