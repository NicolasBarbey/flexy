import { Controller } from '@hotwired/stimulus';
import Splide from '@splidejs/splide';
import '@splidejs/splide/css/core';

class GalleryContoller extends Controller {
  static targets = ['thumbnail', 'root'];

  constructor(arg) {
    super(arg);
    this.main = null;
  }

  initialize() {
    this.main = new Splide(this.rootTarget, {
      pagination: false,
      destroy: this.rootTarget.dataset?.count <= 1,
      breakpoints: {
        768: {
          pagination: true,
          arrows: false
        }
      }
    });
  }
  connect() {
    this.main.mount();

    this.main.on('move', (index) => {
      this.update({ params: { index } });
    });
  }

  update({ params }) {
    const { index: activeIndex } = params;

    this.main.go(activeIndex);

    this.thumbnailTargets.forEach((thumbnail, index) => {
      thumbnail.parentNode.classList.toggle('is-active', index === activeIndex);
    });
  }
}

export default GalleryContoller;
