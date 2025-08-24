import { Controller } from '@hotwired/stimulus';

class StoreDeliveryController extends Controller {
  connect() {
    const buttons = document.querySelectorAll('.StoreDelivery-hours');

    buttons.forEach((button) => {
      button.addEventListener('click', (el) => {
        const parent = el.target.closest('.StoreDelivery');
        const hoursListing = parent.querySelector(
          '.StoreDelivery-hoursListing'
        );
        const isOpen = hoursListing.classList.toggle('md:hidden');
        this.textShowHours(isOpen, button);
      });
    });
  }

  textShowHours(isOpen = true, button) {
    const text = isOpen ? button.dataset.showHours : button.dataset.hideHours;
    button.querySelector('span').textContent = text;
  }
}

export default StoreDeliveryController;
