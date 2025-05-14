import { Controller } from '@hotwired/stimulus';

class PasswordController extends Controller {
  static targets = [
    'input',
    'wrapper',
    'toggler',
    'control',
    'lowercase',
    'size',
    'uppercase',
    'number',
    'special'
  ];
  constructor(...arg) {
    super(...arg);

    this.conditions = {
      size: (value) => value.length >= 12,
      uppercase: (value) => /[A-Z]/.test(value),
      lowercase: (value) => /[a-z]/.test(value),
      number: (value) => /[0-9]/.test(value),
      special: (value) => /[\W_]/.test(value)
    };

    this.indicators = null;
  }

  connect() {
    if (this.hasControlTarget) {
      this.indicators = {
        size: this.sizeTarget ?? null,
        uppercase: this.uppercaseTarget ?? null,
        lowercase: this.lowercaseTarget ?? null,
        number: this.numberTarget ?? null,
        special: this.specialTarget ?? null
      };
      this.inputTarget.addEventListener('focus', () => {
        this.controlTarget.style.display = 'block';
      });
    }

    switchType(this.togglerTarget, this.inputTarget);
  }

  control() {
    const handleConditions = [];
    for (const [condition, check] of Object.entries(this.conditions)) {
      updateIndicator(
        this.indicators[condition],
        check(this.inputTarget.value)
      );
      handleConditions.push(check(this.inputTarget.value));
    }
    this.wrapperTarget?.classList.toggle(
      'FieldInput--error',
      handleConditions.filter((c) => !c).length
    );
  }
}

function switchType(toggler, input) {
  toggler.addEventListener('click', () => {
    input.type = input.type === 'password' ? 'text' : 'password';
    toggler.classList.toggle('is-visible');
  });
}

function updateIndicator(indicator, isValid) {
  if (isValid) {
    indicator.classList.add('valid');
  } else {
    indicator.classList.remove('valid');
  }

  return isValid;
}

export default PasswordController;
