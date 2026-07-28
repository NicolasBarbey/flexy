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

namespace FlexyBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Customer;

class CustomerActivationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer_email', HiddenType::class, [
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ],
            ])
            ->add('activation_code', TextType::class, [
                'label' => Translator::getInstance()->trans('Enter the code received by e-mail'),
                'attr' => [
                    'maxlength' => Customer::CODE_LENGTH,
                    'pattern' => '[0-9]{'.Customer::CODE_LENGTH.'}',
                    'placeholder' => '000000',
                ],
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Length([
                        'min' => Customer::CODE_LENGTH,
                        'max' => Customer::CODE_LENGTH,
                    ]),
                    new Constraints\Regex([
                        'pattern' => '/^[0-9]{'.Customer::CODE_LENGTH.'}$/',
                        'message' => Translator::getInstance()->trans('Activation code must contain only digits'),
                    ]),
                ],
            ]);
    }
}
