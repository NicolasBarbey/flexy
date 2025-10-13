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

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Core\HttpFoundation\Session\Session;

#[AsLiveComponent(name: 'Flexy:PickupPointSearch', template: '@UiComponents/PickupPointSearch/PickupPointSearch.html.twig')]
class PickupPointSearch
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public ?string $errorMessage = '';

    #[LiveProp]
    public string $view = 'map';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly Session $session,
        private readonly DataAccessService $dataAccessService, private readonly TranslatorInterface $translator,
    ) {
    }

    public function getPickupPoints(): array
    {
        return $this->fakePickupPoints();
        $spot = $this->getFirstAddressFromAPi($this->query);
        if (null !== $spot) {
            $place = $spot['properties'];
            $coordinates = $spot['geometry']['coordinates'];

            return $this->fakePickupPoints();

            return $this->dataAccessService->resources(
                '/api/front/delivery_pickup_locations/'.$place['city'].'/'.$place['postcode'],
                [
                    'address' => $place['name'],
                ]
            );
        }

        return [];
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

    #[LiveAction]
    public function setView(#[LiveArg] string $view): void
    {
        $this->view = $view;
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function getFirstAddressFromAPi(string $query): ?array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api-adresse.data.gouv.fr/search/', [
                'query' => [
                    'q' => $query,
                ],
            ]);

            return $response->toArray()['features'][0] ?? null;
        } catch (\Exception $e) {
            $this->errorMessage = $this->translator->trans('No address found for %query%', ['%query%' => $query]);

            return null;
        }
    }

    #[LiveListener('setPickupAddressId')]
    public function getSelectedPickup(#[LiveArg] ?string $id = null): ?string
    {
        if ($id) {
            $this->session->set('pickupAddressId', $id);
        }

        return $this->session->get('pickupAddressId');
    }

    public function fakePickupPoints(): array
    {
        return json_decode('[
    {
        "id": "6661M",
        "latitude": "50.38629900000",
        "longitude": "3.75052000000",
        "title": "LIBRAIRIE NICOLE",
        "address": {
            "id": "6661M",
            "default": 0,
            "label": "",
            "title": "LIBRAIRIE NICOLE",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "LIBRAIRIE NICOLE",
            "address1": "NACFER 79",
            "address2": "",
            "address3": "",
            "zipCode": "7370",
            "city": "Wiheries",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "07:00-13:00",
            "07:00-12:30 13:30-18:00",
            "07:00-12:30 13:30-18:00",
            "07:00-12:30 13:30-18:00",
            "07:00-12:30 13:30-18:00",
            "07:30-13:00",
            null
        ]
    },
    {
        "id": "7096Y",
        "latitude": "50.40687000000",
        "longitude": "3.78556000000",
        "title": "LE RUBAN ENCHANTE",
        "address": {
            "id": "7096Y",
            "default": 0,
            "label": "",
            "title": "LE RUBAN ENCHANTE",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "LE RUBAN ENCHANTE",
            "address1": "RUE SAINT LOUIS 43",
            "address2": "",
            "address3": "",
            "zipCode": "7370",
            "city": "DOUR",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "09:00-12:00 14:00-18:00",
            "09:00-12:00 14:00-18:00",
            "09:00-12:00 14:00-18:00",
            "09:00-12:00 14:00-18:00",
            "09:00-12:00 14:00-18:00",
            "09:00-14:00",
            null
        ]
    },
    {
        "id": "8731N",
        "latitude": "50.42828000000",
        "longitude": "3.74000000000",
        "title": "PASSE-TEMPS-BARY",
        "address": {
            "id": "8731N",
            "default": 0,
            "label": "",
            "title": "PASSE-TEMPS-BARY",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "PASSE-TEMPS-BARY",
            "address1": "RUE FERRER 13A",
            "address2": "",
            "address3": "",
            "zipCode": "7350",
            "city": "Thulin",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "07:00-12:00 13:30-18:00",
            "07:00-12:00 13:30-18:00",
            "07:00-12:00 13:30-18:00",
            "07:00-12:00 13:30-18:00",
            "07:00-12:00 13:30-18:00",
            "08:00-13:00",
            "08:30-12:00"
        ]
    },
    {
        "id": "2599E",
        "latitude": "50.43450240000",
        "longitude": "3.78982510000",
        "title": "LE MAG",
        "address": {
            "id": "2599E",
            "default": 0,
            "label": "",
            "title": "LE MAG",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "LE MAG",
            "address1": "Rue de Caraman 11\/1",
            "address2": "",
            "address3": "",
            "zipCode": "7300",
            "city": "Boussu",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "06:00-12:00 12:00-22:00",
            "06:00-12:00 12:00-22:00",
            "06:00-12:00 12:00-22:00",
            "06:00-12:00 12:00-22:00",
            "06:00-12:00 12:00-22:00",
            "06:00-12:00 12:00-22:00",
            "08:00-12:00 12:00-22:00"
        ]
    },
    {
        "id": "0952F",
        "latitude": "50.43459000000",
        "longitude": "3.68323000000",
        "title": "LIBRAIRIE DE HENSIES",
        "address": {
            "id": "0952F",
            "default": 0,
            "label": "",
            "title": "LIBRAIRIE DE HENSIES",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "LIBRAIRIE DE HENSIES",
            "address1": "RUE HAUTE 36A",
            "address2": "",
            "address3": "",
            "zipCode": "7350",
            "city": "Hensies",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "08:00-12:00 12:00-18:00",
            "08:00-12:00 12:00-18:00",
            "08:00-12:00 12:00-18:00",
            "08:00-12:00 12:00-18:00",
            "08:00-12:00 12:00-18:00",
            "08:00-12:00 12:00-16:00",
            null
        ]
    },
    {
        "id": "8897N",
        "latitude": "50.42207000000",
        "longitude": "3.84453000000",
        "title": "Lecture & gadgets SRL",
        "address": {
            "id": "8897N",
            "default": 0,
            "label": "",
            "title": "Lecture & gadgets SRL",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "Lecture & gadgets SRL",
            "address1": "PLACE DE WASMES 12",
            "address2": "",
            "address3": "",
            "zipCode": "7340",
            "city": "Colfontaine",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "06:00-12:00 12:00-18:00",
            "06:00-12:00 12:00-18:00",
            "06:00-12:00 12:00-18:00",
            "06:00-12:00 12:00-18:00",
            "06:00-12:00 12:00-18:00",
            "07:30-12:00 12:00-18:00",
            "09:00-12:00"
        ]
    },
    {
        "id": "3063G",
        "latitude": "50.41104200000",
        "longitude": "3.85413000000",
        "title": "NUMEDIA",
        "address": {
            "id": "3063G",
            "default": 0,
            "label": "",
            "title": "NUMEDIA",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "NUMEDIA",
            "address1": "RUE DE PATURAGES 148",
            "address2": "",
            "address3": "",
            "zipCode": "7340",
            "city": "Colfontaine",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "06:30-12:00 12:00-19:00",
            "06:30-12:00 12:00-19:00",
            "06:30-12:00 12:00-19:00",
            "06:30-12:00 12:00-19:00",
            "06:30-14:00 15:00-19:00",
            "08:00-12:00 12:00-19:00",
            null
        ]
    },
    {
        "id": "096AH",
        "latitude": "50.43765000000",
        "longitude": "3.83315200000",
        "title": "EPICERIE DU GD-HORNU",
        "address": {
            "id": "096AH",
            "default": 0,
            "label": "",
            "title": "EPICERIE DU GD-HORNU",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "EPICERIE DU GD-HORNU",
            "address1": "RUE DE MONS 119",
            "address2": "",
            "address3": "",
            "zipCode": "7301",
            "city": "HORNU",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "08:30-23:45",
            "08:30-23:45",
            "08:30-23:45",
            "08:30-23:45",
            "08:30-23:45",
            "08:30-23:45",
            "08:30-23:45"
        ]
    },
    {
        "id": "7947E",
        "latitude": "50.39929000000",
        "longitude": "3.87412400000",
        "title": "LIBRAIRIE DU CHAMP PERDU",
        "address": {
            "id": "7947E",
            "default": 0,
            "label": "",
            "title": "LIBRAIRIE DU CHAMP PERDU",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "LIBRAIRIE DU CHAMP PERDU",
            "address1": "RUE DE LA VERDURE 3",
            "address2": "",
            "address3": "",
            "zipCode": "7080",
            "city": "La Bouverie",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "07:00-13:00",
            "07:00-13:00 14:00-18:00",
            "07:00-13:00 14:00-18:00",
            "07:00-13:00 14:00-18:00",
            "07:00-13:00 14:00-18:00",
            "07:00-13:00",
            null
        ]
    },
    {
        "id": "9678E",
        "latitude": "50.47025500000",
        "longitude": "3.79889200000",
        "title": "LIBRAIRIE DAVID",
        "address": {
            "id": "9678E",
            "default": 0,
            "label": "",
            "title": "LIBRAIRIE DAVID",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "LIBRAIRIE DAVID",
            "address1": "RUE OSCAR GILMANT 67",
            "address2": "",
            "address3": "",
            "zipCode": "7333",
            "city": "Tertre",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "06:30-12:00 12:00-13:00",
            "06:30-12:00 12:00-18:00",
            "06:30-12:00 12:00-18:00",
            "06:30-12:00 12:00-18:00",
            "06:30-12:00 12:00-18:00",
            "07:00-12:00 12:00-18:00",
            null
        ]
    },
    {
        "id": "255BM",
        "latitude": "50.40891000000",
        "longitude": "3.89408000000",
        "title": "ZEK PRESS",
        "address": {
            "id": "255BM",
            "default": 0,
            "label": "",
            "title": "ZEK  PRESS",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "ZEK  PRESS",
            "address1": "RUE DES ALLIES 72",
            "address2": "",
            "address3": "",
            "zipCode": "7080",
            "city": "FRAMERIES",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "07:00-19:00",
            "07:00-19:00",
            "07:00-19:00",
            "07:00-19:00",
            "07:00-19:00",
            "07:00-19:00",
            "08:00-13:00"
        ]
    },
    {
        "id": "237AJ",
        "latitude": "50.48250200000",
        "longitude": "3.76339700000",
        "title": "CHEZ WENDY",
        "address": {
            "id": "237AJ",
            "default": 0,
            "label": "",
            "title": "CHEZ WENDY",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "CHEZ WENDY",
            "address1": "GRAND PLACE 12",
            "address2": "",
            "address3": "",
            "zipCode": "7334",
            "city": "SAINT-GHISLAIN",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "07:00-18:00",
            "07:00-18:00",
            "07:00-18:00",
            "07:00-18:00",
            "07:00-18:00",
            "07:00-18:00",
            "07:00-18:00"
        ]
    },
    {
        "id": "1541K",
        "latitude": "50.44780000000",
        "longitude": "3.88609000000",
        "title": "BEL ESPOIR EXPORT",
        "address": {
            "id": "1541K",
            "default": 0,
            "label": "",
            "title": "BEL ESPOIR EXPORT",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "BEL ESPOIR EXPORT",
            "address1": "AVENUE DU CHAMPS DE BATAILLE 6",
            "address2": "",
            "address3": "",
            "zipCode": "7012",
            "city": "Jemappes",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "10:00-12:00 12:00-18:00",
            "10:00-12:00 12:00-18:00",
            "10:00-12:00 12:00-18:00",
            "10:00-12:00 12:00-18:00",
            "10:00-12:00 12:00-18:00",
            null,
            null
        ]
    },
    {
        "id": "9036W",
        "latitude": "50.33962000000",
        "longitude": "3.90567000000",
        "title": "LA COUQUE AU BEURRE",
        "address": {
            "id": "9036W",
            "default": 0,
            "label": "",
            "title": "LA COUQUE AU BEURRE",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "LA COUQUE AU BEURRE",
            "address1": "DE LAVENIR 17",
            "address2": "",
            "address3": "",
            "zipCode": "7040",
            "city": "AULNOIS",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "07:00-18:00",
            "07:00-18:00",
            "07:00-18:00",
            "07:00-18:00",
            "07:00-18:00",
            "07:00-14:00",
            null
        ]
    },
    {
        "id": "5900M",
        "latitude": "50.43140000000",
        "longitude": "3.91869000000",
        "title": "DIAMOND",
        "address": {
            "id": "5900M",
            "default": 0,
            "label": "",
            "title": "DIAMOND",
            "firstName": "",
            "lastName": "",
            "cellphoneNumber": "",
            "phoneNumber": "",
            "company": "DIAMOND",
            "address1": "AVENUE DE LA GRANDE BARRE 19",
            "address2": "",
            "address3": "",
            "zipCode": "7033",
            "city": "Cuemes",
            "countryCode": "BE",
            "additionalData": []
        },
        "moduleId": 43,
        "moduleOptionCode": null,
        "openingHours": [
            "11:00-12:00 12:00-18:00",
            "11:00-12:00 12:00-18:00",
            "11:00-12:00 12:00-18:00",
            "11:00-12:00 12:00-18:00",
            "11:00-12:00 12:00-18:00",
            "11:00-12:00 12:00-18:00",
            "11:00-12:00 12:00-18:00"
        ]
    }
]', true);
    }
}
