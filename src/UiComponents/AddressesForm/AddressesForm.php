<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FlexyBundle\UiComponents\AddressesForm;

use FlexyBundle\UiComponents\Checkout\CheckoutEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Form\Definition\FrontForm;
use Thelia\Log\Tlog;
use Thelia\Model\AddressQuery;
use Thelia\Service\Model\AddressService;
use TwigEngine\Service\FormService;

#[AsLiveComponent(name: "Flexy:AddressesForm", template: '@UiComponents/AddressesForm/AddressesForm.html.twig')]
class AddressesForm extends BaseFrontController
{
  use ComponentToolsTrait;
  use ComponentWithFormTrait;
  use DefaultActionTrait;

  #[LiveProp]
  public ?int $addressId = null;

  public function __construct(
    private readonly FormService $formService,
    private readonly AddressService $addressService
  ) {}

  protected function instantiateForm(): FormInterface
  {
    $formName = $this->addressId ? FrontForm::ADDRESS_UPDATE : FrontForm::ADDRESS_CREATE;

    $form = $this->formService->getFormByName($formName, $this->getData());
    $form->remove('state');
    $form->remove('address3');
    $form->remove('company');

    return $form;
  }

  private function getData(): array
  {
    if (!$this->addressId) {
      return [];
    }
    $address = AddressQuery::create()->findPk($this->addressId);

    return $this->addressService->mapModelToFormData($address);
  }

  #[LiveAction]
  public function save(): void
  {
    $this->checkAuth();

    $this->submitForm();
    if (!$this->getForm()->isValid()) {
      return;
    }
    try {
      $this->addressService->updateOrCreateAddress($this->addressId, $this->getForm());
      $this->emitUp(CheckoutEvents::ADD_NEW_DELIVERY_ADDRESS);
    } catch (\Exception $e) {
      Tlog::getInstance()->error(sprintf('Error during address creation process : %s', $e->getMessage()));
    }
  }
}
