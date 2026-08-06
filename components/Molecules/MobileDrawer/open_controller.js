import { Controller } from "@hotwired/stimulus";

// Toggles a MobileDrawer owned by another component, when the trigger cannot live next to it
// (the category page's "filter & sort" button sits in the Subheader, the drawer in
// Layouts/CategoryFilters). Mirrors Molecules/Modal/open_controller.js.
//
// idValue must point at the element carrying the MobileDrawer controller, not at the drawer
// panel itself. Reusing that controller's own toggle() keeps the open logic in one place
// (is-open class, inert, shared body scroll lock).
export default class extends Controller {
  static values = { id: String };

  toggle() {
    const host = document.getElementById(this.idValue);

    if (!host) {
      return;
    }

    this.application
      .getControllerForElementAndIdentifier(host, "Molecules--MobileDrawer--base")
      ?.toggle();
  }
}
