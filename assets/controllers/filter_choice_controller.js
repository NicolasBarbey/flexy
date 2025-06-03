import { Controller } from '@hotwired/stimulus';

class FilterChoiceController extends Controller {
  static targets = ['label'];

  updateLabel({ params }) {
    if (this.labelTarget) {
      this.labelTarget.dataset.selectLabel = params.label;
    }
  }
}

export default FilterChoiceController;
