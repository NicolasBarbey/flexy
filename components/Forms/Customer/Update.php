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

namespace FlexyBundle\Components\Forms\Customer;

use FlexyBundle\Form\CustomerUpdateForm;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Core\Form\FormServiceInterface;
use Thelia\Domain\Customer\CustomerFacade;

#[AsLiveComponent]
class Update
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    /** @var array<string, mixed>|null */
    #[LiveProp]
    public ?array $customer = null;

    public function __construct(
        private readonly FormServiceInterface $formService,
        private readonly CustomerFacade $customerFacade,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        // No caller feeds this prop today: the account page renders the component bare
        // and the session customer is the sole source. The prop stays as an entry point
        // for a caller that already holds the data.
        if (null === $this->customer && null !== $customer = $this->customerFacade->getCurrentCustomer()) {
            $this->customer = [
                'id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'firstname' => $customer->getFirstname(),
                'lastname' => $customer->getLastname(),
            ];
        }

        return $this->formService->getFormByName(CustomerUpdateForm::FORM_NAME, $this->customer ?? []);
    }
}
