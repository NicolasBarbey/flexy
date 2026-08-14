import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["toggler"];

  connect() {
    this.onKeydown = this.onKeydown.bind(this);
    this.onDocumentClick = this.onDocumentClick.bind(this);

    this.element.addEventListener("keydown", this.onKeydown);
    document.addEventListener("click", this.onDocumentClick);
  }

  disconnect() {
    this.element.removeEventListener("keydown", this.onKeydown);
    document.removeEventListener("click", this.onDocumentClick);
  }

  onKeydown({ key }) {
    if (key === "Escape") {
      this.togglerTarget.classList.remove("is-open");
      this.togglerTarget.focus();
    }
  }

  onDocumentClick(event) {
    if (!this.element.contains(event.target)) {
      this.togglerTarget.classList.remove("is-open");
    }
  }

  toggle({ currentTarget }) {
    currentTarget.classList.toggle("is-open");
  }
}
