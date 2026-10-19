import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        number: String,
    };

    connect() {
        this.update();
    }

    update() {
        const form = this.element.form;

        if (!form) {
            return;
        }

        const decimalSeparator = form.querySelector(
            '[name$="[decimalSeparator]"]'
        );

        if (!decimalSeparator) {
            return;
        }

        const row = decimalSeparator.closest('.form-group');

        if (!row) {
            return;
        }

        row.hidden = this.element.value !== this.numberValue;
    }
}
