import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        visibleInputTypes: Array,
    };

    connect() {
        this.update();
    }

    async update() {
        const optionsRow = this.findOptionsRow();

        if (!optionsRow) {
            return;
        }

        const inputType = await this.fetchInputType();

        this.updateVisibility(optionsRow, inputType);
    }

    async fetchInputType() {
        const fieldTypeId = this.element.value;

        if (!fieldTypeId) {
            return null;
        }

        const url = this.urlValue.replace(
            '__ID__',
            encodeURIComponent(fieldTypeId),
        );

        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            return null;
        }

        const data = await response.json();

        return data.inputType;
    }

    updateVisibility(optionsRow, inputType) {
        optionsRow.hidden = !this.visibleInputTypesValue.includes(
            inputType,
        );
    }

    findOptionsRow() {
        const row = this.element.closest('.row');

        if (!row) {
            return null;
        }

        return row.querySelector(
            '[data-field-type-options-field]',
        );
    }
}
