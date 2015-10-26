define(['underscore', 'backbone', './mock'
], function (_, Backbone, Mock) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        tagName: 'div',
        className: 'action-item',
        template: _.template($('#item-action').html()),
        viewTemplate: _.template($('#item-action-view').html()),
        editTemplate: _.template($('#item-action-edit').html()),

        events: {
            'click .delete': 'remove',
            'click .save': 'saveItem',
            'click .edit': 'editItem',
            'change select': 'changeSelectView'
        },

        initialize: function () {
            this.mock = Mock;
        },

        render: function(template) {
            this.$el.html(this.template());
            $('.view-action', this.$el).html(template);
            return this;
        },

        renderView: function () {
            return this.render(this.viewTemplate());
        },

        renderEdit: function() {
            return this.render(this.editTemplate());
        }
    });
});
