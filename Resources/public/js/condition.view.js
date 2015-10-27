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

            this.listenTo(this.collection, 'toJson', this.toJson);

            if (_.isObject(this.mock)) {
                this.render();
            }
        },

        toJson: function() {
            this.$el.children('input').val(JSON.stringify(this.collection.toJSON()));
        },

        addCondition: function () {
            var defaultCondition = {
                "target": "ticket",
                "condition": "neq",
                "property": "subject"
            };

            var item = new ConditionItemView({"model": new ConditionModel(defaultCondition)});
            this.$('#list-condition').append(item.renderItemEdit().el);
        },

        addGroup: function () {
            var group = new ConditionGroupView({model: new ConditionModel()});
            this.$('#list-condition').append(group.render().el);
        },

        render: function () {
            var parent = this.$('#list-condition');
            this.build(this.mock, parent);
            this.collection.trigger("toJson");
        },

        build: function (mock, parent) {
            var m = this.getAttributes(mock);

            var group,
                hasChildren = !_.isEmpty(mock.children),
                model = new ConditionModel(m);

            this.collection.add(model);

            if (hasChildren) {
                group = new ConditionGroupView({model: model, "collection": this.collection});
                parent.append(group.renderItemView().el);
                parent = group.$el;
            }

            var item = new ConditionItemView({model: model, "collection": this.collection});
            parent.append(item.renderItemView().el);

            if (hasChildren) {
                var that = this;
                _.each(mock.children, function (item) {
                    that.build(item, parent);
                });
            }
        },

        getAttributes: function (data) {
            var general = {
                "id": data.id,
                "condition": data.condition,
                "weight": data.weight,
                "target": data.target,
                "active": data.active
            };

            if (_.has(data, "expression")) {
                general["expression"] = data.expression;
            }

            if (_.has(data, "property")) {
                general["property"] = data.property;
            }

            if (_.has(data, "value")) {
                general["value"] = data.value;
            }

            if (_.has(data, "parent")) {
                general["parent"] = data.parent;
            }

            return general;
        }
    });
});
