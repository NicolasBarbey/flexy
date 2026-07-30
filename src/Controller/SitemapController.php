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

use FlexyBundle\Service\SitemapGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Model\LangQuery;

class SitemapController extends FlexyController
{
    #[Route('/sitemap', name: 'front_sitemap')]
    #[Route('/sitemap.xml', name: 'front_sitemap_xml')]
    public function generate(SitemapGenerator $sitemapGenerator): Response
    {
        $request = $this->getRequest();

        $lang = $request->query->get('lang', '');
        $context = $request->query->get('context', '');
        $flush = $request->query->get('flush', false);

        if ('' !== $lang && !$this->checkLang($lang)) {
            $this->pageNotFound();
        }

        if (!\in_array($context, ['', 'catalog', 'content'], true)) {
            $this->pageNotFound();
        }

        $cacheItem = $sitemapGenerator->generate(
            $this->getParser(),
            $lang,
            $context,
            (bool) $flush,
        );

        $response = new Response();
        $response->setContent($cacheItem->get());
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

    private function checkLang(string $lang): bool
    {
        return null !== LangQuery::create()->findOneByCode($lang);
    }
}
