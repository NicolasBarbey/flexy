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

namespace FlexyBundle\UiComponents\RegisterValidationCode;

use FlexyBundle\Form\CustomerActivationForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Domain\Customer\Service\CustomerCodeManager;

#[AsLiveComponent(name: 'Flexy:RegisterValidationCode', template: '@UiComponents/RegisterValidationCode/RegisterValidationCode.html.twig')]
class RegisterValidationCode extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?string $email = null;

    #[LiveProp]
    public ?bool $canLogin = false;

    public function __construct(protected CustomerCodeManager $customerCodeProcessor)
    {
    }

    public function mount(?string $email = null): void
    {
        $this->email = $email;
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CustomerActivationForm::class, [
            'customer_email' => $this->email,
        ]);
    }

    #[LiveAction]
    public function updateCode(#[LiveArg] string $code): void
    {
        $this->formValues['activation_code'] = $code;
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        $form = $this->getForm();

        try {
            $this->customerCodeProcessor->activateCustomerByCode(
                $form->get('customer_email')->getData(),
                (string) $form->get('activation_code')->getData()
            );
            $this->canLogin = true;
        } catch (\Exception $e) {
            $this->addFlash('error', 'Activation failed: '.$e->getMessage());
        }
    }
}
