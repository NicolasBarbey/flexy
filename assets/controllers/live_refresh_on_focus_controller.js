import { Controller } from "@hotwired/stimulus";

// Re-renders the LiveComponent it is attached to when the tab becomes visible again or the
// window regains focus, so a change made in another tab shows up here.
//
// Deliberately not a BroadcastChannel: refreshing on focus covers every cause of divergence
// without enumerating them (login/logout and its cart duplication, session expiry, an order
// placed elsewhere, an admin change, a tab opened before the change), and it needs no fallback
// for older browsers.
export default class extends Controller {
  connect() {
    this.isStale = false;

    this.onVisibilityChange = () => {
      if (document.hidden) {
        this.isStale = true;

        return;
      }

      this.refresh();
    };
    this.onBlur = () => {
      this.isStale = true;
    };
    this.onFocus = () => this.refresh();

    document.addEventListener("visibilitychange", this.onVisibilityChange);
    window.addEventListener("blur", this.onBlur);
    window.addEventListener("focus", this.onFocus);
  }

  disconnect() {
    document.removeEventListener("visibilitychange", this.onVisibilityChange);
    window.removeEventListener("blur", this.onBlur);
    window.removeEventListener("focus", this.onFocus);
  }

  // The staleness flag also answers the first focus after a page load: the markup is fresh
  // until the page has been left at least once, so no request is made then.
  refresh() {
    if (!this.isStale || this.hasUnsubmittedInput()) {
      return;
    }

    const live = this.application.getControllerForElementAndIdentifier(this.element, "live");

    if (!live) {
      // Silently doing nothing here is how this controller gets misused: it only has an effect
      // on an element that also carries the `live` controller.
      console.warn("live-refresh-on-focus: no live controller on this element, nothing to refresh", this.element);

      return;
    }

    this.isStale = false;
    live.$render();
  }

  // A re-render replaces the subtree, so anything the visitor has typed or ticked but not yet
  // sent to the server would be thrown away — leaving the page alone is the lesser evil, and
  // the next real interaction refreshes it anyway. Comparing against the `default*` properties
  // is what makes this self-clearing: they hold the values the last render wrote.
  hasUnsubmittedInput() {
    return Array.from(this.element.querySelectorAll("input, textarea, select")).some((field) => {
      if (field.type === "checkbox" || field.type === "radio") {
        return field.checked !== field.defaultChecked;
      }

      if (field.tagName === "SELECT") {
        return Array.from(field.options).some((option) => option.selected !== option.defaultSelected);
      }

      return field.value !== field.defaultValue;
    });
  }
}
