import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
class QuantityController extends Controller {
  static targets = ["input"];

  inputTargetConnected(element) {
    element.addEventListener("keyup", this.enforceMinMax.bind(this, element));
    element.addEventListener(
      "keypress",
      this.enforceNumberOnly.bind(this, element),
    );
  }
  inputTargetDisconnected(element) {
    element.removeEventListener(
      "keyup",
      this.enforceMinMax.bind(this, element),
    );
    element.removeEventListener(
      "keypress",
      this.enforceNumberOnly.bind(this, element),
    );
  }

  decrement() {
    const min = parseInt(this.inputTarget.getAttribute("min"));
    const value = parseInt(this.inputTarget.value) || 0;

    if (min && value <= min) {
      return;
    }
    this.inputTarget.value = value - 1;
  }
  increment() {
    const max = parseInt(this.inputTarget.getAttribute("max"));
    const value = parseInt(this.inputTarget.value) || 0;

    if (max && value >= max) {
      return;
    }
    this.inputTarget.value = value + 1;
  }

  enforceNumberOnly(el, e) {
    if (e.key.length === 1 && !/[0-9]/.test(e.key)) {
      e.preventDefault();
    }
  }

  enforceMinMax(el, e) {
    if (el.value !== "") {
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
