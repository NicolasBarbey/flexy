import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = { class: { type: String, default: "is-stuck" } };

    connect() {
        this.sentinel = document.createElement("div");
        this.sentinel.setAttribute("aria-hidden", "true");
        this.sentinel.style.cssText = "position:absolute;width:1px;height:1px;top:0;visibility:hidden;pointer-events:none;";
        this.element.before(this.sentinel);

        this.observer = new IntersectionObserver(([entry]) => {
            this.element.classList.toggle(this.classValue, !entry.isIntersecting);
        });
        this.observer.observe(this.sentinel);
    }

    disconnect() {
        this.observer?.disconnect();
        this.sentinel?.remove();
    }
}
