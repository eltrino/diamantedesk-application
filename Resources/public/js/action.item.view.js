define(['underscore', 'backbone', './mock'
], function (_, Backbone, Mock) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        tagName: 'div',
        className: 'action-item',
        template: _.template($('#item-action').html()),

        events: {
            'click .delete': 'remove',
            'click .save': 'saveItem',
            'click .edit': 'editItem',
            'change select': 'changeSelectView'
        },

        initialize: function () {
            this.mock = Mock;
        },

        render: function() {
            this.$el.html(this.template());
            return this;
        }
    });
});
