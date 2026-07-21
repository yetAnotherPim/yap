import './stimulus_bootstrap.js';
import './styles/admin.css';


//TODO: if sortable
//TODO: demonstzrate that elements are draggable


import {Sortable} from "sortablejs";

//TODO: on new item: set max position

//TODO: import sortable collection an place code there
//TODO: register as plugin for targets
const collections = document.querySelectorAll('div.field-collection');
collections.forEach(function(coll) {
    const targetNode = coll.querySelector(':scope > div.form-widget > div.ea-form-collection-items > div.accordion > div.form-widget-compound > div');

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
