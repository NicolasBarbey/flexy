import { Controller } from '@hotwired/stimulus';

class HeaderController extends Controller {
  static targets = ['panel', 'back', 'sub'];

  constructor(arg) {
    super(arg);
    this.previous = 0;
  }

  // data-action="header#togglePanel" data-header-panel-param="menu|lang|search"
  togglePanel(event) {
    const { panel: panelId } = event.params;
    const target = this.#findPanel(panelId);
    if (!target) return;

    const isOpen = target.classList.contains('is-open');
    this.#closeAll();

    if (!isOpen) {
      target.classList.add('is-open');
      document.body.classList.add('locked');
      event.currentTarget.classList.add('is-selected');
    }
  }

  close() {
    this.#closeAll();
    if (this.hasBackTarget) this.backTarget.dataset.menuBack = -1;
    this.subTargets.forEach((sub) => sub.classList.remove('is-active'));
  }

  displaySubMenu({ params }) {
    const { item } = params;

    this.subTargets.forEach((sub) => sub.classList.remove('is-active'));

    const targetSub = this.subTargets.find(
      (sub) => sub.dataset.menuSub === item.toString()
    );

    if (targetSub) {
      this.previous = targetSub.dataset.menuPrevious;
      targetSub.classList.add('is-active');

      if (this.previous !== undefined) {
        const previousSub = this.subTargets.find(
          (s) => s.dataset.menuSub === this.previous
        );
        if (previousSub) {
          previousSub.classList.add('is-active');
        }
      }
    } else {
      this.previous = -1;
    }

    if (this.hasBackTarget) this.backTarget.dataset.menuBack = this.previous;
  }

  back(event) {
    this.displaySubMenu({
      params: { item: event.currentTarget.dataset.menuBack }
    });
  }

  #findPanel(panelId) {
    return this.panelTargets.find((p) => p.dataset.headerPanelId === panelId);
  }

  #closeAll() {
    this.panelTargets.forEach((p) => p.classList.remove('is-open'));
    document.body.classList.remove('locked');
    this.element
      .querySelectorAll('[data-header-panel-param]')
      .forEach((btn) => btn.classList.remove('is-selected'));
  }
}

export default HeaderController;
