import { Controller } from '@hotwired/stimulus';

class QuantityController extends Controller {
  async initialize() {
    for (const element of this.element.querySelectorAll(
      'input[type="number"]'
    )) {
      element.addEventListener('keyup', () => {
        this.enforceMinMax(element);
      });
      element.addEventListener('change', () => {
        this.enforceMinMax(element);
      });
    }
  }

  enforceMinMax(el) {
    if (el.value !== '') {
      if (parseInt(el.value) < parseInt(el.min)) {
        el.value = el.min;
      }
      if (parseInt(el.value) > parseInt(el.max)) {
        el.value = el.max;
      } else {
        el.value = parseInt(el.value);
      }
    }
  }
}

export default QuantityController;
