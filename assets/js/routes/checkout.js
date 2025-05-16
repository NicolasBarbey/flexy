import {
  pickupPoint,
  pickupPointHours
} from '@components/Organisms/Card/PickupPoint/PickupPoint';
import { deliveryModule } from '@utils/delivery';
import { PickupPointView } from '@components/Organisms/Modules/PickupPointModule/pickupPointView';

function delivery() {
  document.body.classList.remove('no-js');
  deliveryModule();
  pickupPointHours();
  pickupPoint();
  PickupPointView();
}

document.addEventListener('DOMContentLoaded', () => {
  delivery();
});
