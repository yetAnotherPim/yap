import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'items',
        'fieldType',
    ];

    static values = {
        index: Number,
        prototype: String,
        prototypeName: String,
        fieldUrl: String,
    };

    async add() {
        if (this.hasFieldTypeTarget) {
            await this.addField();

            return;
        }

        this.addTranslation();
    }

    async addField() {
        const fieldTypeId = this.fieldTypeTarget.value;

        if (!fieldTypeId) {
            return;
        }

        if (this.hasFieldType(fieldTypeId)) {
            return;
        }

        const url = this.fieldUrlValue.replace(
            '__id__',
            fieldTypeId,
        );

        const response = await fetch(url);

        if (!response.ok) {
            return;
        }

        const html = await response.text();

        this.itemsTarget.insertAdjacentHTML(
            'beforeend',
            html,
        );

        this.fieldTypeTarget.value = '';
    }

    addTranslation() {
        const index = this.indexValue++;

        const html = this.prototypeValue.replaceAll(
            this.prototypeNameValue,
            index,
        );

        this.itemsTarget.insertAdjacentHTML(
            'beforeend',
            html,
        );
    }

    remove(event) {
        event.currentTarget
            .closest('[data-batch-edit-item]')
            .remove();
    }

    hasFieldType(fieldTypeId) {
        return [
            ...this.itemsTarget.querySelectorAll(
                '[data-field-type-id]',
            ),
        ].some(
            item => item.dataset.fieldTypeId === fieldTypeId,
        );
    }
}
