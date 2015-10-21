define(['underscore',
    'backbone',
    './condition.collection',
    './condition.model',
    './condition.item.view',
    './condition.group.view',
    './mock'
], function (_,
             Backbone,
             ConditionCollection,
             ConditionModel,
             ConditionItemView,
             ConditionGroupView,
             Mock) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        el: '#condition-block',

        events: {
            'click #add-rule-condition': '_addCondition',
            'click #add-rule-group': '_addGroup'
        },

        /**
         * Constructor
         */
        initialize: function () {
            this.mock = Mock.data;
            this.collection = new ConditionCollection();

            if (_.isObject(this.mock)) {
                this.render();
            }
        },

        _addCondition: function () {
            var item = new ConditionItemView({model: new ConditionModel()});
            this.$('#list-condition').append(item.render().el);
        },

        _addGroup: function () {
            var group = new ConditionGroupView({model: new ConditionModel()});
            this.$('#list-condition').append(group.render().el);
        },

        render: function () {
            var parent = this.$('#list-condition');
            this.build(this.mock, parent);

        },

        build: function (mock, parent) {
            var group,
                isGroup = !_.has(mock, 'condition'),
                model = new ConditionModel();

            model.set(mock);
            if (isGroup) {
                group = new ConditionGroupView({model: model});
                parent.append(group.render().el);
                parent = group.$el;
            } else {
                var item = new ConditionItemView({model: model});
                parent.append(item.render().el);
            }

            if (!_.isEmpty(mock.children)) {
                var that = this;
                _.each(mock.children, function (item) {
                    that.build(item, parent);
                });
            }
        },

        /**
         * Fill data to collections from hidden inputs
         *
         * @returns {*}
         * @private
         */
        _prepareCollections: function () {
            var rule = $.parseJSON($(this.options.fieldId).val());

            this.getCollection().reset(rule);
            return this;
        }
    });
});
