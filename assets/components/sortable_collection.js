import { Sortable } from "sortablejs";

export default {
    SELECTORS: {
        COLLECTION: 'div.sortable-collection',
        COLLECTION_ITEM: '.field-collection-item',
        TARGET_NODE: ':scope > div.form-widget > div.ea-form-collection-items > div.accordion > div.form-widget-compound > div',
        POSITION_INPUT: 'input[name*="[position]"]',
        TITLE_UPDATE_PREFIX: '[data-title-update-selector-prefix]',
        HAS_ERROR: '.has-error',
        TITLE_ELEMENT: '.accordion-button'
    },

    init() {
        this.registerEventListener();
        this.applySortable();
        this.initValidation();
    },

    registerEventListener() {
        document.addEventListener('position:updated', (event) => {
            this.updatePositions(event);
        });
        document.addEventListener('ea.collection.item-added', (event) => {
            const {newElement} = event.detail;
            this.dispatchUpdateEvent(newElement.parentNode);
        });
        document.addEventListener('ea.collection.item-removed', (event) => {
            const {collection} = event.detail;
            this.dispatchUpdateEvent(collection.querySelector(this.SELECTORS.TARGET_NODE));
        });

        document.addEventListener('change', (event) => {
            const collection = event.target.closest(this.SELECTORS.TITLE_UPDATE_PREFIX);

            if (!collection) {
                return;
            }

            const prefix = collection.dataset.titleUpdateSelectorPrefix;

             if (!event.target.matches(`[id^="${prefix}"][id$="_autocomplete"]`)) {
                return;
            }

             this.updateTitle(event.target);
        });
    },

    updateTitle(target) {
        const title = target.options[target.selectedIndex].text ?? '';

        const item = target.closest(this.SELECTORS.COLLECTION_ITEM);

        if (!item) {
            return;
        }

        const titleElement = item.querySelector(this.SELECTORS.TITLE_ELEMENT);

        if (!titleElement) {
            return;
        }

        [...titleElement.childNodes]
            .filter(node =>
                node.nodeType === Node.TEXT_NODE &&
                node.textContent.trim() !== ''
            )
            .forEach(node => node.remove());

        titleElement.append(document.createTextNode(` ${title}`));
    },

    initValidation() {
        document.addEventListener('ea.form.error', (event) => {

            const { form } = event.detail;
            const field = form.querySelector(this.SELECTORS.HAS_ERROR);

            if (!field) {
                return;
            }

            const collapse = field.closest('.accordion-collapse');

            if (!collapse) {
                return;
            }

            const scrollToField = () => {
                field.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });

                field.focus({ preventScroll: true });
            };

            if (collapse.classList.contains('show')) {
                scrollToField();
                return;
            }

            collapse.addEventListener('shown.bs.collapse', scrollToField, {
                once: true,
            });

            bootstrap.Collapse.getOrCreateInstance(collapse, {
                toggle: false,
            }).show();
        });
    },

    updatePositions(event) {
        const { targetNode } = event.detail;

        if(null === targetNode.closest(this.SELECTORS.COLLECTION)) {
            return;
        }

        const positionInputs = targetNode.querySelectorAll(this.SELECTORS.POSITION_INPUT);

        positionInputs.forEach((elt, index) => {
            elt.value = index;
        });
    },

    dispatchUpdateEvent(targetNode) {
        const event = new CustomEvent('position:updated', {
            bubbles: true,
            detail: { targetNode }
        });
        targetNode.dispatchEvent(event);
    },

    applySortable() {
        const collections = document.querySelectorAll(this.SELECTORS.COLLECTION);

        collections.forEach((collection) => {
            const targetNode = collection.querySelector(this.SELECTORS.TARGET_NODE);

            if (!targetNode) {
                return;
            }

            Sortable.create(targetNode, {
                animation: 150,
                onEnd: () => {
                    this.dispatchUpdateEvent(targetNode);
                }
            });
        });
    },
};


