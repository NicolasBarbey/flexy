import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["panel", "back", "sub"];

  constructor(arg) {
    super(arg);
    this.previous = 0;
    this.selectedButton = null;
  }

  // Bound here rather than in connect(), which can run more than once per instance: rebinding
  // would leave the previous listener unremovable.
  initialize() {
    this.onResize = this.onResize.bind(this);
  }

  connect() {
    window.addEventListener("resize", this.onResize);
  }

  disconnect() {
    window.removeEventListener("resize", this.onResize);
  }

  // Crossing a breakpoint with a panel still open has to revisit the decision — the menu becomes
  // a dropdown bar past md and must stop locking the page. The removed CSS overrides used to get
  // this for free from media queries.
  onResize() {
    this.syncBodyLock();
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
  // Only a panel that is actually fixed locks the page: the same markup is a fullscreen
  // overlay below its breakpoint and in-flow (or a header-level overlay) above it, where the
  // page must stay scrollable. Kept identical in Molecules/MobileDrawer/base_controller.js,
  // the other writer of this class.
  syncBodyLock() {
    const open = document.querySelectorAll(".Header-menu.is-open, .MobilePanel.is-open, .MobileDrawer.is-open");

    document.body.classList.toggle(
      "locked",
      [...open].some((panel) => getComputedStyle(panel).position === "fixed"),
    );
  }
}
