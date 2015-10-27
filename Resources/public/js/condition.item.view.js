define(['underscore', 'backbone', './mock', './condition.collection'
], function (_, Backbone, Mock, ConditionCollection) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        tagName: 'div',
        className: 'condition-item',
        template: _.template($('#item-condition').html()),

        events: {
            'click .delete': 'remove',
            'click .save': 'saveItem',
            'click .edit': 'editItem',
            'change select': 'changeElement',
            'change input': 'changeElement'
        },

        initialize: function () {
            this.mock = Mock;
            this.prepareTemplates();

            this.listenTo(this.model, 'change', this.redrawEdit);
            this.listenTo(this.model, 'change:actionObject', this.changeModelField);
            this.listenTo(this.model, 'change:target', this.changeModelField);
        },

        changeModelField: function () {
            var defaultProperty = _.first(_.keys(this.getProperties())),
                defaultCondition = _.first(_.keys(this.getConditions()));

            this.model.set({'condition': defaultCondition, 'property': defaultProperty});
        },

        prepareTemplates: function () {
            this.editTemplate = _.template($(this.getEditTemplateId()).html());
            this.viewTemplate = _.template($(this.getViewTemplateId()).html());
        },

        redrawEdit: function () {
            this.prepareTemplates();
            $('.edit-condition', this.$el).html(this.renderEdit());
            this.fillModel();
        },

        editItem: function () {
            $('.edit-condition', this.$el).html(this.renderEdit());
            $('.edit-condition, .save', this.$el).removeClass('x-hide');
            $('.view-condition, .edit', this.$el).addClass('x-hide');
        },

        saveItem: function () {
            $('.view-condition', this.$el).html(this.renderView());
            $('.edit-condition, .save', this.$el).addClass('x-hide');
            $('.view-condition, .edit', this.$el).removeClass('x-hide');

            this.collection.trigger("toJson");
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

        changeElement: function (e) {
            var el = $(e.target),
                property = el.data('property'),
                value = el.val();

            this.model.set(property, value);
        },

        render: function (template, container) {
            this.$el.html(this.template());
            $(container, this.$el).html(template);
            return this;
        },

        renderItemView: function () {
            return this.render(this.renderView(), '.view-condition');
        },

        renderItemEdit: function () {
            var template = this.render(this.renderEdit(), '.edit-condition');
            $('.save', this.$el).addClass('x-hide');
            $('.edit-condition, .edit', this.$el).removeClass('x-hide');

            return template;
        },

        renderEdit: function () {
            var data = {
                'targets': this.mock.targets,
                'conditions': this.getConditions(),
                'actionObject': this.mock.actionObject,
                'properties': this.getProperties(),
                'attrs': this.model.attributes
            };

            return this.editTemplate(data);
        },

        renderView: function () {
            return this.viewTemplate(this.model.attributes);
        },

        getConditions: function () {
            var attrs = this.model.attributes,
                conditions = {};

            _.each(this.mock.conditions, function (item, key) {
                var c = [attrs.target, attrs.actionObject];
                var res = _.intersection(item.keywords, c);
                if (res.length == c.length) {
                    conditions[key] = item;
                }
            });

            return conditions;
        },

        getProperties: function () {
            return this.mock.targets[this.model.attributes.target]['properties'];
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

            _.each(this.mock.conditionTemplates, function (template) {
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
