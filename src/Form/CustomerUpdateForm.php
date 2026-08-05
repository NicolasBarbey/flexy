<?php

declare(strict_types=1);

namespace FlexyBundle\Form;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Thelia\Core\Security\SecurityContext;
use Thelia\Core\Translation\Translator;
use Thelia\Model\ConfigQuery;
use Thelia\Model\CustomerQuery;
use Symfony\Component\Validator\Constraints;

class CustomerUpdateForm extends CustomerRegisterForm
{
    public const FORM_NAME = 'flexybundle_form_customer_update_form';

    public function __construct(
        #[Autowire(service: 'translator')]
        TranslatorInterface $translation,
        private readonly SecurityContext $security,
    ) {
        parent::__construct($translation);
    }

    public function buildForm(): void
    {
        parent::buildForm();

        $this->formBuilder->remove('password');
        $this->formBuilder->remove('email');

        $canUpdateEmail = (bool) ConfigQuery::read('customer_change_email', 0);

        // When the shop forbids email changes the field is read-only and only shows
        // the current address. It must carry no constraint: Symfony still validates
        // a disabled field, and the customer has no way to fix a violation on it.
        $this->formBuilder->add('email', EmailType::class, [
            'constraints' => $canUpdateEmail ? [
                new Constraints\NotBlank(),
                new Constraints\Email(),
                new Constraints\Callback(
                    [$this, 'verifyExistingEmail']
                ),
            ] : [],
            'required' => $canUpdateEmail,
            'label' => Translator::getInstance()->trans('Email Address'),
            'disabled' => !$canUpdateEmail,

            'help' => !$canUpdateEmail ? $this->translator->trans('Si vous voulez changer d\'adresse mail, contactez nous.') : null,

        ]);
    }

    public function verifyExistingEmail($value, ExecutionContextInterface $context): void
    {
        $customer = CustomerQuery::create()->findOneByEmail($value);

        // The customer's own address is not a duplicate.
        if (null === $customer || $customer->getId() === $this->security->getCustomerUser()?->getId()) {
            return;
        }

        $context->addViolation($this->translation->trans('This email is already used'));
    }

    public static function getName(): string
    {
        return self::FORM_NAME;
    }
}
