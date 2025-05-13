import { Controller } from '@hotwired/stimulus';

class SimpleSliderController extends Controller {
  static targets = ['input'];
  connect() {
    const slider = this.element;

    const watcher = {
      isDown: false,
      startX: 0,
      scrollLeft: 0
    };

    slider.addEventListener('mousedown', (e) =>
      start(e.pageX, slider, watcher)
    );
    slider.addEventListener('touchstart', (e) =>
      start(e.touches[0].pageX, slider, watcher)
    );

    slider.addEventListener('mouseleave', () => end(slider, watcher));
    slider.addEventListener('touchend', () => end(slider, watcher));

    slider.addEventListener('mouseup', () => end(slider, watcher));
    slider.addEventListener('touchcancel', () => end(slider, watcher));

    slider.addEventListener('mousemove', (e) =>
      move(e, e.pageX, slider, watcher)
    );
    slider.addEventListener('touchmove', (e) =>
      move(e, e.touches[0].pageX, slider, watcher)
    );
  }
}

function start(pageX, slider, watcher) {
  watcher.isDown = true;
  slider.classList.add('active');
  watcher.startX = pageX - slider.offsetLeft;
  watcher.scrollLeft = slider.scrollLeft;
}
function end(slider, watcher) {
  watcher.isDown = false;
  slider.classList.remove('active');
}

function move(e, pageX, slider, watcher) {
  if (!watcher.isDown) return;
  e.preventDefault();
  const x = pageX - slider.offsetLeft;
  const walk = (x - watcher.startX) * 3; // Adjust the scroll speed here
  slider.scrollLeft = watcher.scrollLeft - walk;
}

export default SimpleSliderController;
