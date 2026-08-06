import { Controller } from "@hotwired/stimulus";
import { getComponent } from "@symfony/ux-live-component";

// Bridges plain form/select input to the LiveComponent's save() action. Filter checkboxes are
// watched through a single change listener rather than one data-action per pill, since the pills
// are rendered by the form theme.
export default class extends Controller {
  async initialize() {
    this.component = await getComponent(this.element);
  }

  filterChange() {
    this.component.action("save").then(() => this.afterSave());
  }

  sortChange(event) {
    this.component.action("save", { sort: event.target.value }).then(() => this.afterSave());
  }

  reset() {
    this.component.action("save", { reset: true }).then(() => this.afterSave(true));
  }

  afterSave(reset = false) {
    this.resyncDrawer();
    this.broadcastTotal();
    // Nudge range sliders (Fields/RangeSlider) to redraw their progress bar: the LiveComponent
    // patches the inputs in place without reconnecting their controller, so it needs a signal —
    // and a reset must snap them back to full range.
    window.dispatchEvent(new CustomEvent(reset ? "live:form:reset" : "live:form:save"));
  }

  // The subheader's mobile/tablet product count lives outside this LiveComponent, so a save()
  // re-render never reaches it. The re-rendered grid carries the fresh total in a data attribute;
  // broadcast it so the count controller (Layouts/Subheader/count) can mirror it in place.
  broadcastTotal() {
    const total = this.element.querySelector(".CategoryFilters-grid")?.dataset.categoryTotal;
    if (total === undefined) {
      return;
    }
    document.dispatchEvent(new CustomEvent("category-filters:total", { detail: { total: Number(total) } }));
  }

  // A LiveComponent re-render patches attributes (inert included) onto the existing DOM node
  // in place — it doesn't disconnect/reconnect Stimulus controllers, so the drawer's own
  // connect()-time inertness check never re-runs on its own after a save(). This controller is
  // the one place that already knows about the LiveComponent lifecycle (it drives the save()
  // calls above), so it nudges the drawer rather than teaching the generic, widely-reused
  // MobileDrawer about LiveComponent internals.
  resyncDrawer() {
    this.application
      .getControllerForElementAndIdentifier(this.element, "Molecules--MobileDrawer--base")
      ?.syncInertness();
  }
}
