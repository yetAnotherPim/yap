import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        visibleInputTypes: Array,
        duplicateMessage: String,
    };

    connect() {
        this.updateOptions();
    }

    async update() {
        this.showDuplicateWarning();
        await this.updateOptions();
    }

    async updateOptions() {
        const optionsRow = this.findOptionsRow();

        if (!optionsRow) {
            return;
        }

        const inputType = await this.fetchInputType();

        this.updateVisibility(optionsRow, inputType);
    }

    showDuplicateWarning() {
        this.element
            .closest('.form-widget')
            ?.querySelector('[data-duplicate-warning]')
            ?.remove();

        const value = this.element.value;

        if (!value) {
            return;
        }

        const form = this.element.closest('form');

        if (!form) {
            return;
        }

        const isDuplicate = [...form.querySelectorAll(
            '[data-controller~="product-type-field-configuration-options"]',
        )].some(
            (element) =>
                element !== this.element
                && element.value === value,
        );

        if (!isDuplicate) {
            return;
        }

        const warning = document.createElement('div');

        warning.dataset.duplicateWarning = '';
        warning.classList.add('form-text', 'text-warning');
        warning.textContent = this.duplicateMessageValue;

        this.element.closest('.form-widget')?.append(warning);
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
