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

namespace FlexyBundle\UiComponents\Checkout\PromoCodeForm;

use FlexyBundle\Form\AddressEditForm;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Translation\Translator;
use Thelia\Domain\Addressing\Service\AddressService;
use Thelia\Domain\Customer\CustomerFacade;
use Thelia\Form\CouponCode;
use Thelia\Model\AddressQuery;
use Thelia\Model\Customer;
use TwigEngine\Service\FormService;

#[AsLiveComponent(name: 'Flexy:Checkout:PromoCodeForm', template: '@UiComponents/Checkout/PromoCodeForm/PromoCodeForm.html.twig')]
class PromoCodeForm extends BaseFrontController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public function __construct(
        private readonly FormService $formService,
        private readonly FormFactoryInterface $formFactory,
    ) {}

    protected function instantiateForm(): FormInterface
    {
        $form = $this->formService->getFormByName(CouponCode::getName(), []);

        $form->add('submit', SubmitType::class, [
            'label' => Translator::getInstance()->trans('Apply'),
        ]);

        return $form;
    }

    #[LiveAction]
    public function save()
    {
        $this->submitForm();
        dd($this->getForm()->getData());
    }
}
