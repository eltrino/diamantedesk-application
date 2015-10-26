define(['underscore',
    'backbone',
    './action.item.view'
], function (_,
             Backbone,
             ActionItemView) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        el: '#action-block',

        events: {
            'click #add-rule-action': 'addAction'
        },

        /**
         * Constructor
         */
        initialize: function () {

        },

        addAction: function () {
            var item = new ActionItemView();
            this.$('#list-action').append(item.renderEdit().el);
        }
    });
});
