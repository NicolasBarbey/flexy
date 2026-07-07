import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['select', 'content', 'frame', 'main'];
    static classes = ['previewing'];

    setWidth() {
        const width = this.selectTarget.value;

        if (!width) {
            const hash = this.currentHash();

            this.contentTarget.hidden = false;
            this.frameTarget.hidden = true;
            this.mainTarget.classList.remove(...this.previewingClasses);

            // Same requirement as the iframe branch below: the browser resolves a #hash
            // scroll position against the layout at the time it's set, so it must be
            // re-applied after the content is visible again, not before.
            if (hash) {
                requestAnimationFrame(() => {
                    window.location.hash = '';
                    window.location.hash = hash;
                });
            }
            return;
        }

        const hash = this.currentHash();
        const alreadyLoaded = !!this.frameTarget.src;

        if (!alreadyLoaded) {
            this.frameTarget.src = `${window.location.pathname}?embed=1${hash}`;
        }

        this.frameTarget.style.width = `${width}px`;
        this.contentTarget.hidden = true;
        this.frameTarget.hidden = false;
        this.mainTarget.classList.add(...this.previewingClasses);

        // Le hash doit être réappliqué APRÈS le reflow déclenché par le
        // redimensionnement, sinon le scroll se calcule sur l'ancien layout.
        if (alreadyLoaded && hash) {
            requestAnimationFrame(() => {
                const frameLocation = this.frameTarget.contentWindow.location;
                frameLocation.hash = '';
                frameLocation.hash = hash;
            });
        }
    }

    navigate(event) {
        if (this.frameTarget.hidden) {
            return;
        }

        event.preventDefault();
        this.frameTarget.contentWindow.location.hash = new URL(event.currentTarget.href).hash;
    }

    currentHash() {
        if (!this.frameTarget.hidden && this.frameTarget.contentWindow) {
            return this.frameTarget.contentWindow.location.hash;
        }

        return window.location.hash;
    }
}
