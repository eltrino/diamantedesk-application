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
            'click #add-rule-condition': 'addCondition',
            'click #add-rule-group': 'addGroup'
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

        addCondition: function () {
            var data = {
                "target": "ticket",
                "condition": "neq",
                "property": "subject"
            };

            var item = new ConditionItemView({"model": new ConditionModel(data)});
            this.$('#list-condition').append(item.renderItemEdit().el);
        },

        addGroup: function () {
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
                model = new ConditionModel(mock);

            if (isGroup) {
                group = new ConditionGroupView({model: model});
                parent.append(group.render().el);
                parent = group.$el;
            } else {
                var item = new ConditionItemView({model: model});
                parent.append(item.renderItemView().el);
            }

            if (!_.isEmpty(mock.children)) {
                var that = this;
                _.each(mock.children, function (item) {
                    that.build(item, parent);
                });
            }
        }
    });
});
