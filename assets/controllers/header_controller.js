import { Controller } from '@hotwired/stimulus';

class HeaderController extends Controller {
  static targets = ['toggler', 'menu', 'close', 'back', 'sub'];

  constructor(arg) {
    super(arg);
    this.previous = 0;
  }

  connect() {
    this.togglerTarget?.addEventListener('click', () =>
      this.menuTarget.classList.toggle('is-open')
    );

    this.backTarget.addEventListener('click', () => {
      displaySubMenu(this.previous);
    });
  }

  close() {
    this.menuTarget.classList.remove('is-open');
    this.backTarget.dataset.menuBack = -1;
    this.subTargets.forEach((sub) => {
      sub.classList.remove('is-active');
    });
  }

  sub(e) {
    displaySubMenu(
      e.currentTarget.dataset.menuItem,
      this.subTargets,
      this.previous,
      this.backTarget
    );
  }

  back() {
    displaySubMenu(
      this.previous,
      this.subTargets,
      this.previous - 1,
      this.backTarget
    );
  }
}

function displaySubMenu(current, subs = [], previous, back) {
  subs.forEach((sub) => {
    sub.classList.remove('is-active');

    if (sub.dataset.menuSub === current) {
      previous = sub.dataset.menuPrevious;
      sub.classList.add('is-active');
      [...subs]
        .find((s) => s.dataset.menuSub === previous)
        ?.classList.add('is-active');
      back.dataset.menuBack = previous ?? -1;
    }
  });
}

export default HeaderController;
