import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import { debounce } from 'lodash';

class RegisterCodeController extends Controller {
  static targets = ['input', 'code','submit'];
  async initialize() {
    this.component = await getComponent(this.element);
    this.checkValidity = debounce(this.checkValidity, 400).bind(this);
  }

  connect() {
    this.element.addEventListener('paste', (e) => {
      e.preventDefault();

      const chars = e.clipboardData.getData('text').split('');

      this.inputTargets.forEach((input, index) => {
        input.value = chars[index];
      });

      this.checkValidity();
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
    this.checkValidity();
  }

  checkValidity() {
    if (this.inputTargets.every((input) => input.value.trim() !== '')) {
      this.component.action('updateCode', {code : this.inputTargets.map(input => input.value).join('') });
      this.toggleSubmit(true);
      return;
    }
    this.toggleSubmit(false);
    this.component.action('updateCode', {code : '' });
  }

  toggleSubmit(display = false) {
    this.submitTarget.parentNode.classList.toggle('hidden', !display);
    this.submitTarget.disabled =  !display;
  }

  beforeSave(e) {
    e.preventDefault();
    console.log(this.codeTarget.value);
    this.component.action('save');
    this.toggleSubmit(false);
    this.inputTargets.forEach((input) => input.value = '');
  }
}

export default RegisterCodeController;
