define(['underscore', 'backbone', './mock'
], function (_, Backbone, Mock) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        tagName: 'div',
        className: 'condition-item',
        template: _.template($('#item-condition').html()),
        editTemplate: _.template($('#item-condition-edit').html()),
        viewTemplate: _.template($('#item-condition-view').html()),

        events: {
            'click .delete': 'remove',
            'click .save': 'saveItem',
            'click .edit': 'editItem',
            'change select': 'changeSelectView'
        },

        initialize: function () {
            this.mock = Mock;
            this.listenTo(this.model, 'change', this.render);
        },

        editItem: function () {
            $('.edit-condition', this.$el).html(this.renderEdit());
            $('.edit-condition, .save', this.$el).removeClass('x-hide');
            $('.view-condition, .edit', this.$el).addClass('x-hide');
        },

        saveItem: function () {
            var target = this.$('select.target').val(),
                condition = this.$('select.condition').val(),
                value = this.$('input.value').val();

            this.model.set({'target': target, 'condition': condition, 'value': value}).trigger('change');
            $('.view-condition', this.$el).html(this.renderView());
        },

        changeSelectView: function (e) {
            var selectEl = $(e.target),
                selected = $(':selected', selectEl).text();

            selectEl.siblings('span').html(selected);
        },

        render: function () {
            this.$el.html(this.template());
            $('.view-condition', this.$el).html(this.renderView());
            return this;
        },

        renderView: function () {
            return this.viewTemplate({
                target: this.model.attributes.target,
                condition: this.model.attributes.condition
            });
        },

        renderEdit: function () {
            return this.editTemplate({'targets': this.mock.targets, 'conditions': this.mock.conditions});
        }
    });
});
