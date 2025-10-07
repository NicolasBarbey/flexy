<?php

namespace FlexyBundle\UiComponents\AccountHero;

use FlexyBundle\Controller\FlexyController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Flexy:AccountHero', template: '@UiComponents/AccountHero/AccountHero.html.twig')]
class AccountHero extends FlexyController
{
}
