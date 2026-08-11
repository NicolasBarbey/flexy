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

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The Front module still declares the Thelia 2 URLs (front.xml) in its own router and
 * renders Smarty-era view names this theme does not ship. Declaring the same paths here
 * wins over the Front module router (the default router is matched first) and sends
 * visitors to the Flexy equivalent.
 *
 * GET only: the POST counterparts stay with the Front module, no Flexy template posts
 * to them. The two address action URLs (delete, make-default) redirect to the address
 * list rather than to their Flexy action — a permanent redirect should not carry a side
 * effect, and nothing in this theme links to them.
 */
class LegacyRouteController extends FlexyController
{
    #[Route('/register', name: 'legacy_register', methods: ['GET'])]
    public function register(): RedirectResponse
    {
        return $this->generateRedirect($this->generateUrl('customer_register'), 301);
    }

    #[Route('/login', name: 'legacy_login', methods: ['GET'])]
    public function login(): RedirectResponse
    {
        return $this->generateRedirect($this->generateUrl('customer_login'), 301);
    }

    #[Route('/logout', name: 'legacy_logout', methods: ['GET'])]
    public function logout(): RedirectResponse
    {
        return $this->generateRedirect($this->generateUrl('customer_logout'), 301);
    }

    #[Route('/account/update', name: 'legacy_account_update', methods: ['GET'])]
    public function accountUpdate(): RedirectResponse
    {
        return $this->generateRedirect($this->generateUrl('account_index'), 301);
    }

    #[Route('/account/password', name: 'legacy_account_password', methods: ['GET'])]
    public function accountPassword(): RedirectResponse
    {
        return $this->generateRedirect($this->generateUrl('account_index'), 301);
    }

    #[Route('/address/create', name: 'legacy_address_create', methods: ['GET'])]
    public function addressCreate(): RedirectResponse
    {
        return $this->generateRedirect($this->generateUrl('account_address_new'), 301);
    }

    #[Route('/address/update/{addressId}', name: 'legacy_address_update', requirements: ['addressId' => '\d+'], methods: ['GET'])]
    public function addressUpdate(int $addressId): RedirectResponse
    {
        return $this->generateRedirect($this->generateUrl('account_address', ['addressId' => $addressId]), 301);
    }

    #[Route('/address/delete/{addressId}', name: 'legacy_address_delete', requirements: ['addressId' => '\d+'], methods: ['GET'])]
    #[Route('/address/default/{addressId}', name: 'legacy_address_default', requirements: ['addressId' => '\d+'], methods: ['GET'])]
    public function addressList(): RedirectResponse
    {
        return $this->generateRedirect($this->generateUrl('account_addresses'), 301);
    }

    #[Route('/password', name: 'legacy_password', methods: ['GET'])]
    public function password(): RedirectResponse
    {
        return $this->generateRedirect($this->generateUrl('password_forgotten'), 301);
    }

    #[Route('/password-sent', name: 'legacy_password_sent', methods: ['GET'])]
    public function passwordSent(): RedirectResponse
    {
        return $this->generateRedirect($this->generateUrl('password_reset_link'), 301);
    }
}
