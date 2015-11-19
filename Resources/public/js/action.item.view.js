define(['underscore', 'backbone', './actions.mock'
], function (_, Backbone, Mock) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        tagName: 'div',
        className: 'action-item',
        template: _.template($('#item-action').html()),

        events: {
            'click .delete': 'removeItem',
            'click .save': 'saveItem',
            'click .edit': 'editItem',
            'change select': 'changeElement',
            'change input': 'changeElement'
        },

        initialize: function () {
            this.mock = Mock;
            this.prepareTemplates();

            this.listenTo(this.model, 'change', this.redrawEdit);
            this.listenTo(this.model, 'change:type', this.onTypeChange);
            this.listenTo(this.model, 'change:target', this.onTargetChange);
        },

        redrawEdit: function () {
            this.prepareTemplates();
            $('.edit-action', this.$el).html(this.renderEdit());
            this.fillModel();
        },

        fillModel: function () {
            var elements = this.$('select, input'),
                that = this;

            this.model.clear({silent: true});
            _.each(elements, function (item) {
                var el = that.$(item);
                that.model.set(el.data('property'), el.val(), {silent: true});
            });
        },

        onTargetChange: function () {
            var defaultProperty = this.getDefaultProperty(this.model.get('target'));

            this.model.set({'property': defaultProperty});
        },

        onTypeChange: function (model, type) {
            var defaults;

            if ('UpdateProperty' == type) {
                var defaultTarget = this.getDefaultTarget(),
                    defaultProperty = this.getDefaultProperty(defaultTarget);
                defaults = {'target': defaultTarget, 'property': defaultProperty};
            } else {
                var defaultNotification = this.getDefaultNotification(),
                    defaultAddressee = this.getDefaultAddressee();
                defaults = {'notification': defaultNotification, 'addressee': defaultAddressee};
            }

            this.model.set(defaults);
        },

        getDefaultNotification: function () {
            return _.first(_.keys(this.mock.notifications));
        },

        getDefaultAddressee: function () {
            return _.first(_.keys(this.mock.addressees));
        },

        getDefaultTarget: function () {
            return _.first(_.keys(this.mock.targets));
        },

        getDefaultProperty: function (target) {
            return _.first(_.keys(this.getProperties(target)));
        },

        getProperties: function (target) {
            if (!target) {
                return null;
            }
            return this.mock.targets[target]['properties'];
        },

        changeElement: function (e) {
            var el = $(e.target),
                property = el.data('property'),
                value = el.val();

            this.model.set(property, value);
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
            $('.edit', this.$el).addClass('x-hide');
            $('.edit-action, .save', this.$el).removeClass('x-hide');

            return template;
        },

        renderEdit: function () {
            var data = {
                'types': this.mock.types,
                'targets': this.mock.targets,
                'notifications': this.mock.notifications,
                'addressees': this.mock.addressees,
                'attrs': this.model.attributes,
                'properties': this.getProperties(this.model.get('target'))
            };

            return this.editTemplate(data);
        },

        renderView: function () {
            var data = {
                'types': this.mock.types,
                'targets': this.mock.targets,
                'notifications': this.mock.notifications,
                'addressees': this.mock.addressees,
                'attrs': this.model.attributes
            };

            return this.viewTemplate(data);
        },

        removeItem: function () {
            this.collection.remove(this.model);
            this.remove();
            this.collection.trigger("toJson");
        },

        prepareTemplates: function () {
            this.editTemplate = _.template($(this.getEditTemplateId()).html());
            this.viewTemplate = _.template($(this.getViewTemplateId()).html());
        },

        getViewTemplateId: function () {
            return '#' + this.getTemplateId() + '-view';
        },

        getEditTemplateId: function () {
            return '#' + this.getTemplateId() + '-edit';
        },

        getTemplateId: function () {
            var that = this,
                templateId;

            _.each(this.mock.actionTemplates, function (template) {
                var isSuitable = true;

                if (!_.isEmpty(templateId)) {
                    return;
                }

                _.each(template.depends, function (item, key) {
                    if (_.indexOf(item, that.model.get(key)) < 0) {
                        isSuitable = false;
                    }
                });

                if (isSuitable) {
                    templateId = template.id;
                }
            });

            if (_.isEmpty(templateId)) {
                throw new Error('Template not found');
            }

            return templateId;
        }
    });
});
