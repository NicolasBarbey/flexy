import { Controller } from '@hotwired/stimulus';
import { trans, EXPIRED } from '../translator';

class HelloController extends Controller {
  connect() {
    this.element.textContent = `Hello Stimulus! Edit me in assets/controllers/hello_controller.js \n
       test translation: ${trans(EXPIRED)}`;
  }
}

export default HelloController;
