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

namespace FlexyBundle\Components\Forms\CustomerInformation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Core\Form\FormServiceInterface;

#[AsLiveComponent]
class Base extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public array $formData = [];

    public function __construct(
        private readonly FormServiceInterface $formService,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formService->getFormByName('flexybundle_form_customer_informations_form', $this->formData);
    }
}
