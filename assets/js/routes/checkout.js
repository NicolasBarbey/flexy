import {
  pickupPoint,
  pickupPointHours
} from '@components/Organisms/Card/PickupPoint/PickupPoint';
import { PickupPointView } from '@components/Organisms/Modules/PickupPointModule/pickupPointView';

function delivery() {
  document.body.classList.remove('no-js');
  pickupPointHours();
  pickupPoint();
  PickupPointView();
}

document.addEventListener('DOMContentLoaded', () => {
  delivery();
});
