import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    update() {
        const locale = this.element.value;
        const form = this.element.closest('form');

        if (!form) {
            return;
        }

        for (const field of form.querySelectorAll(
            '[data-title-translation-locale]',
        )) {
            field.hidden =
                field.dataset.titleTranslationLocale !== locale;
        }
    }
}
