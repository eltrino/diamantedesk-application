define(['underscore', 'backbone'
], function (_, Backbone) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        tagName: 'div',
        className: 'condition-group',
        viewTemplate: _.template($('#group-condition-view').html()),

        initialize: function () {
            var that = this,
                events = {
                    'click .delete': 'remove',
                    'click .save': 'saveGroup',
                    'click .edit': 'editGroup',
                    'change select': 'changeExpression'
                };

            that.events = {};

            _.each(events, function (handler, event) {
                var actionSelector = event + '.' + that.cid;
                that.events[actionSelector] = handler;
            });
        },

        editGroup: function () {
            var select = this.$el.children('.selector');

            this.$el.children('.x-view').addClass('x-hide');
            this.$el.children('.x-edit').removeClass('x-hide');
            select.css('display', 'inline-block').children('.x-edit').removeClass('x-hide');
        },

        saveGroup: function () {
            var expression = this.$el.children('div').children('.x-edit').val();
            this.model.set('expression', expression);

            this.$el.children('.match').html(expression);
            this.$el.children('.x-edit').addClass('x-hide');
            this.$el.children('.x-view').removeClass('x-hide');
            this.$el.children('.selector').css('display', 'none');
        },

        changeExpression: function (e) {
            var select = $(e.target);
            select.siblings('span').html(select.val());
        },

        render: function () {
            this.$el.html(this.viewTemplate());
            return this;
        }
    });
});
