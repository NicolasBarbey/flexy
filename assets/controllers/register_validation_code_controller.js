import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

class RegisterCodeController extends Controller {
  static targets = ['input'];
  async initialize() {
    this.component = await getComponent(this.element);
  }

  connect() {
    this.element.addEventListener('paste', (e) => {
      e.preventDefault();

      const chars = e.clipboardData.getData('text').split('');

      this.inputTargets.forEach((input, index) => {
        input.value = chars[index];
      });

      this.beforeSave();
    });
  }

  input({ target }) {
    const currentIndex = this.inputTargets.indexOf(target);

    target.focus();

    const chars = target.value.split('');
    const value = chars.shift();

    target.value = value ?? '';

    const nextInput = this.inputTargets[currentIndex + 1];

    if (chars.length && nextInput) {
      nextInput.value = chars.join('');

      this.input({ target: nextInput });
    }
    this.beforeSave();
  }

  beforeSave() {
    if (this.inputTargets.every((input) => input.value.trim() !== '')) {
      this.component.action('save');
    }
  }
}

export default RegisterCodeController;
