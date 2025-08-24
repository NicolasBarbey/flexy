import { pickupPoint } from '@components/Organisms/Card/PickupPoint/PickupPoint';
import { PickupPointView } from '@components/Organisms/Modules/PickupPointModule/pickupPointView';

function delivery() {
  pickupPoint();
  PickupPointView();
}

document.addEventListener('DOMContentLoaded', () => {
  delivery();
});
