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

namespace FlexyBundle\Components\Forms\RegisterValidationCode;

use FlexyBundle\Form\CustomerActivationForm;
use Propel\Runtime\Exception\PropelException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Domain\Customer\Service\CustomerCodeManager;

#[AsLiveComponent]
class Base extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?string $email = null;

    #[LiveProp]
    public bool $activated = false;

    public function __construct(
        private readonly CustomerCodeManager $customerCodeProcessor,
        private readonly LoggerInterface $logger,
        #[Autowire(service: 'translator')]
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function mount(?string $email = null): void
    {
        $this->email = $email;
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CustomerActivationForm::class, ['customer_email' => $this->email]);
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
            $this->activated = true;
        } catch (PropelException $e) {
            $this->logger->error('Customer activation failed: '.$e->getMessage());
            $this->addFlash('error', $this->translator->trans('An unexpected error occurred. Please try again later.'));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }
}
