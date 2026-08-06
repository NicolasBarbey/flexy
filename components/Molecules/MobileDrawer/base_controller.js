import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["drawer"];

  connect() {
    // Bound once so the same reference can be added and removed (window listeners are not
    // cleaned up implicitly when the element leaves the DOM).
    this.syncInertness = this.syncInertness.bind(this);
    this.syncInertness();
    window.addEventListener("resize", this.syncInertness);
  }

  disconnect() {
    window.removeEventListener("resize", this.syncInertness);
  }

  toggle(event) {
    if (this.isStatic()) {
      return;
    }

    // A consumer may hide this instance above a breakpoint (e.g. FilterSelect's mobile
    // drawer, replaced by its own desktop dropdown) while still wiring a click on a
    // visible trigger to this action. Ignore it then: toggling a hidden drawer would still
    // lock page scroll with nothing visible to show for it.
    if (getComputedStyle(this.drawerTarget).display === "none") {
      return;
    }

    // Bound to keydown.space too (see the trigger's data-action): prevent the default page
    // scroll a bare <div data-action> would otherwise trigger on Space.
    event?.preventDefault();

    const isOpen = this.drawerTarget.classList.toggle("is-open");
    // While closed, the drawer sits off-screen via `transform` (not display/visibility), so
    // its content must be explicitly excluded from focus/tab order rather than relying on it
    // being visually hidden.
    this.drawerTarget.inert = !isOpen;
    this.syncBodyLock();
  }

  close() {
    if (this.isStatic()) {
      return;
    }

    this.drawerTarget.classList.remove("is-open");
    this.drawerTarget.inert = true;
    this.syncBodyLock();

    // Closing shouldn't leave focus stuck on now-hidden content. This also closes any purely
    // CSS ":focus"-driven popup sharing this same trigger (e.g. FilterSelect's desktop
    // dropdown), which has no JS state of its own to close.
    if (this.element.contains(document.activeElement)) {
      document.activeElement.blur();
    }
  }

  // Past its static breakpoint (Base.css `all: revert`, staticFrom="md"|"lg"), the drawer
  // stops being a fixed bottom sheet and becomes plain inline content — there is no trigger
  // left to open it, so its content must never stay `inert` there. `position` is read from
  // the CSS rather than duplicating the breakpoint value here, so this stays correct
  // whichever `staticFrom` a given instance uses.
  isStatic() {
    return getComputedStyle(this.drawerTarget).position !== "fixed";
  }

  syncInertness() {
    this.drawerTarget.inert = !this.isStatic() && !this.drawerTarget.classList.contains("is-open");
  }

  // "locked" is a single shared class on <body>: derive it from whether any drawer or
  // Header panel is open rather than toggling it per instance, so multiple lock sources
  // on the same page don't desync each other's lock state.
  syncBodyLock() {
    document.body.classList.toggle(
      "locked",
      document.querySelector(".MobileDrawer.is-open, .Header-menu.is-open, .MobilePanel.is-open") !== null,
    );
  }
}
