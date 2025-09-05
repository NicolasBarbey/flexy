<?php declare(strict_types=1);
namespace FlexyBundle\UiComponents\InvoiceCard;
use FlexyBundle\UiComponents\AddressCard\AddressCard;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Flexy:InvoiceCard', template: '@UiComponents/InvoiceCard/InvoiceCard.html.twig')]
class InvoiceCard extends AddressCard
{
}
