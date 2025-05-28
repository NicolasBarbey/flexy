import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

class LangSelectController extends Controller {
  static targets = ['toggler'];

  async initialize() {
    this.component = await getComponent(this.element);
  }
  connect() {
    this.element.addEventListener('keydown', ({ key }) => {
      if (key === 'Escape') {
        this.togglerTarget.focus();
        this.toggle();
      }
    });

    document.addEventListener('click', (event) => {
      if (!this.element.contains(event.target)) {
        this.element.classList.remove('is-open');
      }
    });
  }

  toggle() {
    this.element.classList.toggle('is-open');
  }
}

export default LangSelectController;
