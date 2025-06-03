import { Controller } from '@hotwired/stimulus';

class DrawerController extends Controller {
  static targets = ['drawer'];

  toggle() {
    this.drawerTarget.classList.toggle('is-open');
    this.toggleOverlay(true);
    document.body.classList.toggle('locked');
  }

  close() {
    this.drawerTarget.classList.remove('is-open');
    this.toggleOverlay(false);
    document.body.classList.remove('locked');
  }

  toggleOverlay(open) {
    if (open) {
      const overlay = document.createElement('div');
      overlay.classList.add('MobileDrawer-overlay');
      overlay.addEventListener('click', () => this.close());
      return this.element.appendChild(overlay);
    }

    document.querySelector('.MobileDrawer-overlay')?.remove();
  }
}

export default DrawerController;
