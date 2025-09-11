import { Controller } from '@hotwired/stimulus';

class StoreDeliveryController extends Controller {
  static targets = ['btn','list'];

  toggle({currentTarget}) {
    const isOpen = this.listTarget.classList.toggle('md:hidden');
    this.textShowHours(isOpen, currentTarget);

  }
  textShowHours(isOpen = true, button) {
    button.textContent = isOpen ? button.dataset.showHours : button.dataset.hideHours;
  }
}

export default StoreDeliveryController;
