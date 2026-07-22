import { Sortable } from "sortablejs";

//TODO: title update
export default {
    SELECTORS: {
        COLLECTION: 'div.sortable-collection',
        TARGET_NODE: ':scope > div.form-widget > div.ea-form-collection-items > div.accordion > div.form-widget-compound > div',
        POSITION_INPUT: 'input[name*="[position]"]'
    },

    init() {
        this.registerEvents();
        this.applySortable();
    },

    registerEvents() {
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
    },

    updatePositions(event) {
        const { targetNode } = event.detail;
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


