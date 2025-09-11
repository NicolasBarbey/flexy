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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Customer;

class CustomerActivationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer_email', HiddenType::class,
                [
                    'constraints' => [
                        new Constraints\NotBlank([
                            'message' => Translator::getInstance()->trans('Email is required'),
                        ]),
                        new Constraints\Email([
                            'message' => Translator::getInstance()->trans('Please enter a valid email address'),
                        ]),
                    ],
                ]
            )->add('activation_code', HiddenType::class, [
                'attr' => [
                    'maxlength' => Customer::CODE_LENGTH,
                    'pattern' => '[0-9]{'.Customer::CODE_LENGTH.'}',
                    'placeholder' => '000000',
                    'data-register-validation-code-target' => 'code',
                ],
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => Translator::getInstance()->trans('Activation code is required'),
                    ]),
                    new Constraints\Length([
                        'min' => Customer::CODE_LENGTH,
                        'max' => Customer::CODE_LENGTH,
                        'exactMessage' => Translator::getInstance()->trans('Activation code must be exactly {{ limit }} digits'),
                        'minMessage' => Translator::getInstance()->trans('Activation code is too short ({{ limit }} digits required)'),
                        'maxMessage' => Translator::getInstance()->trans('Activation code is too long ({{ limit }} digits maximum)'),
                    ]),
                    new Constraints\Regex([
                        'pattern' => '/^[0-9]{'.Customer::CODE_LENGTH.'}$/',
                        'message' => Translator::getInstance()->trans('Activation code must contain only '.Customer::CODE_LENGTH.' digits'),
                    ]),
                ],
            ])->add('submit', SubmitType::class, [
                'label' => Translator::getInstance()->trans('Confirm my registration'),
                'row_attr' => [
                    'class' => 'flex justify-center mt-8 hidden',
                ],
                'attr' => [
                    'data-register-validation-code-target' => 'submit',
                    'data-action' => 'register-validation-code#beforeSave',
                ],
            ]);
    }
}
