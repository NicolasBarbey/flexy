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

namespace FlexyBundle\UiComponents\PickupPointSearch;

use FlexyBundle\Form\Type\PickupAddressType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsLiveComponent(name: 'Flexy:PickupPointSearch', template: '@UiComponents/PickupPointSearch/PickupPointSearch.html.twig')]
class PickupPointSearch extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public $initialFormData;

    #[LiveProp]
    public array $pickups = [];

    #[LiveProp]
    public ?array $selectedPickup = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private DataAccessService $dataAccessService,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(PickupAddressType::class, $this->initialFormData);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        $data = $this->getForm()->getData();

        $response = $this->httpClient->request('GET', 'https://api-adresse.data.gouv.fr/search/', [
            'query' => [
                'q' => $data['address'],
            ],
        ]);
        $feature = $response->toArray()['features'][0];
        $place = $feature['properties'];
        $coordinates = $feature['geometry']['coordinates'];
        $this->pickups = $this->dataAccessService->resources(
            '/api/front/delivery_pickup_locations/'.$place['city'].'/'.$place['postcode'],
            [
                'address' => $place['name'],
            ]
        );
        $this->dispatchBrowserEvent('pickuppoint:update', ['pickups' => $this->pickups, 'coordinates' => $coordinates]);
    }

    #[LiveAction]
    public function pickupPointClick(#[LiveArg] $pickup): void
    {
        $this->selectedPickup = $pickup;
    }

    #[LiveAction]
    public function updateOption(#[LiveArg] string $id): void
    {
        $current = array_filter($this->pickups, fn ($item) => $item['id'] === $id);

        $this->selectedPickup = reset($current);

        $this->dispatchBrowserEvent('pickup:selected', ['pickup' => $this->selectedPickup]);
    }
}
