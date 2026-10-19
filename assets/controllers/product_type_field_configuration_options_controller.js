import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        visibleInputTypes: Array,
        duplicateMessage: String,
    };

    connect() {
        this.updateFieldOptions();
    }

    async update() {
        this.showDuplicateWarning();
        await this.updateFieldOptions();
    }

    async updateFieldOptions() {
        const optionsRow = this.findOptionsRow();
        const translationsRow = this.findTranslationsRow();

        if (!optionsRow && !translationsRow) {
            return;
        }

        const fieldType = await this.fetchFieldType();

        if(optionsRow) {
            this.updateOptionsVisibility(optionsRow, fieldType?.inputType ?? null);
        }

        if(translationsRow) {
            this.updateTranslationsVisibility(translationsRow, fieldType?.isTranslatable ?? false);
        }
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

    async fetchFieldType() {
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

        return await response.json();
    }

    updateOptionsVisibility(optionsRow, inputType) {
        optionsRow.hidden = !this.visibleInputTypesValue.includes(
            inputType,
        );
    }

    updateTranslationsVisibility(translationsRow, translatable) {
        translationsRow.hidden = !translatable;

        translationsRow
            .querySelectorAll('input, textarea, select, button')
            .forEach((element) => {
                element.disabled = !translatable;
            });
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

    findTranslationsRow() {
        const row = this.element.closest('.row');

        if (!row) {
            return null;
        }

        return row.querySelector(
            '[data-field-type-translations-field]',
        );
    }
}
