import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["confirm"];

  open() {
    this.element.showModal();
  }

  close() {
    this.element.close();
  }
}
