import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const help = this.element.querySelector('.form-help');

        if (!help) {
            return;
        }

        const text = help.innerText.trim();

        help.innerHTML = `
            <span
                class="text-muted"
                role="button"
                aria-label="Help"
            >
                <i class="fa fa-circle-question"></i>
            </span>
        `;

        const trigger = help.firstElementChild;

        this.tooltip = new window.bootstrap.Tooltip(trigger, {
            title: text,
            placement: 'right',
            customClass: 'help-tooltip',
        });
    }

    disconnect() {
        this.tooltip?.dispose();
    }
}
