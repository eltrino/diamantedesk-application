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

        editItem: function () {
            $('.edit-action', this.$el).html(this.renderEdit());
            $('.edit-action, .save', this.$el).removeClass('x-hide');
            $('.view-action, .edit', this.$el).addClass('x-hide');
        },

        saveItem: function () {
            $('.view-action', this.$el).html(this.renderView());
            $('.edit-action, .save', this.$el).addClass('x-hide');
            $('.view-action, .edit', this.$el).removeClass('x-hide');

            this.collection.trigger("toJson");
        },

        render: function (template, container) {
            this.$el.html(this.template());
            $(container, this.$el).html(template);
            return this;
        },

        renderItemView: function () {
            return this.render(this.renderView(), '.view-action');
        },

        renderItemEdit: function () {
            var template = this.render(this.renderEdit(), '.edit-action');
            $('.save', this.$el).addClass('x-hide');
            $('.edit-action, .edit', this.$el).removeClass('x-hide');

            return template;
        },

        renderEdit: function () {
            var data = {
                'actions': this.mock.data,
                'attrs': this.model.attributes
            };

            return this.editTemplate(data);
        },

        renderView: function () {
            return this.viewTemplate(this.model.attributes);
        }
    });
});
