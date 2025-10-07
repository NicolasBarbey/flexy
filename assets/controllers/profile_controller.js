import { Controller } from '@hotwired/stimulus';

class ProfileController extends Controller {
  static targets = ['dropdown'];

  connect() {
    document.body.addEventListener('click', ({ target }) => {
      if(!this.element.contains(target)) {
        this.hide();
      }
    });
  }

  toggle() {
    this.dropdownTarget.classList.toggle('active');
  }

  hide() {
    this.dropdownTarget.classList.remove('active');
  }
}

export default ProfileController;
