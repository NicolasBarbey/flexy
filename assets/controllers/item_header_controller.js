import { Controller } from '@hotwired/stimulus';

class ItemHeaderController extends Controller {
  static targets = ['toggler'];

  connect() {
    this.element.addEventListener('keydown', ({ key }) => {
      if (key === 'Escape') {
        this.togglerTarget.classList.remove('is-open');
        this.togglerTarget.focus();
      }
    });

    document.addEventListener('click', (event) => {
      if (!this.element.contains(event.target)) {
        this.togglerTarget.classList.remove('is-open');
      }
    });
  }
  toggle({ currentTarget }) {
    currentTarget.classList.toggle('is-open');
  }
}

export default ItemHeaderController;
