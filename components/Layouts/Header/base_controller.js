import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["panel", "back", "sub"];

  constructor(arg) {
    super(arg);
    this.previous = 0;
    this.selectedButton = null;
  }

  togglePanel(event) {
    const { panel: panelId } = event.params;
    const target = this.findPanel(panelId);
    if (!target) return;

    const isOpen = target.classList.contains("is-open");
    this.closeAll();

    if (!isOpen) {
      target.classList.add("is-open");
      this.syncBodyLock();
      event.currentTarget.classList.add("is-selected");
      event.currentTarget.setAttribute("aria-expanded", "true");
      this.selectedButton = event.currentTarget;
    }
  }

  close() {
    this.closeAll();
    if (this.hasBackTarget) this.backTarget.dataset.menuBack = -1;
    this.subTargets.forEach((sub) => sub.classList.remove("is-active"));
  }

  displaySubMenu({ params }) {
    const { item } = params;

    this.subTargets.forEach((sub) => sub.classList.remove("is-active"));

    const targetSub = this.subTargets.find((sub) => sub.dataset.menuSub === item.toString());

    if (targetSub) {
      this.previous = targetSub.dataset.menuPrevious;
      targetSub.classList.add("is-active");

      if (this.previous !== undefined) {
        const previousSub = this.subTargets.find((s) => s.dataset.menuSub === this.previous);
        if (previousSub) {
          previousSub.classList.add("is-active");
        }
      }
    } else {
      this.previous = -1;
    }

    if (this.hasBackTarget) this.backTarget.dataset.menuBack = this.previous;
  }

  back(event) {
    this.displaySubMenu({ params: { item: event.currentTarget.dataset.menuBack } });
  }

  findPanel(panelId) {
    return this.panelTargets.find((p) => p.dataset.headerPanelId === panelId);
  }

  closeAll() {
    this.panelTargets.forEach((p) => p.classList.remove("is-open"));
    this.syncBodyLock();

    if (this.selectedButton) {
      this.selectedButton.classList.remove("is-selected");
      this.selectedButton.setAttribute("aria-expanded", "false");
      this.selectedButton = null;
    }
  }

  // "locked" is a single shared class on <body>: derive it from whether any panel or
  // drawer is open rather than toggling it per instance, so this controller and
  // MobileDrawer don't desync each other's lock state.
  syncBodyLock() {
    document.body.classList.toggle(
      "locked",
      document.querySelector(".Header-menu.is-open, .MobilePanel.is-open, .MobileDrawer.is-open") !== null,
    );
  }
}
