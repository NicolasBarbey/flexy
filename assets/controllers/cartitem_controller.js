import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
  timeout = null;

  async initialize() {
    this.component = await getComponent(this.element);
    window.addEventListener('cartitem:toast', (event) => this.toast(event));
  };

  cancelDelete() {
    clearTimeout(this.timeout);
    this.component.action('cancelDelete');
  };

  // toast event : delete after [timer] seconds
  toast(event) {
    const nbSeconds = event.detail.timer;
    this.timeout    = setTimeout(() => {
      this.component.action('deleteCartItem');
    }, nbSeconds * 1000);
  }
}
