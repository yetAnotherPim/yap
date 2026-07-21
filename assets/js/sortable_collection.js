import './../stimulus_bootstrap.js';
import {Sortable} from "../vendor/sortablejs/sortablejs.index.js";

const collections = document.querySelectorAll('div.field-collection');
collections.forEach(function(coll) {
    const targetNode = coll.querySelector(':scope > div.form-widget > div.ea-form-collection-items > div.accordion > div.form-widget-compound');

    if (targetNode) {
        console.log(targetNode);

        Sortable.create(targetNode, {
            animation: 150,
            onEnd: function (evt) {
                // Update position inputs on drop
                targetNode.querySelectorAll('input[name*="[position]"]').forEach(function(elt, index) {
                    elt.value = index;
                });
            }
        });
    }
});


