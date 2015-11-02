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
        initialize: function (options) {
            this.options = options;
            this.mock = JSON.parse(this.$(this.options.fieldId).val());

            this.collection = new ConditionCollection();

            this.listenTo(this.collection, 'toJson', this.toJson);

            if (_.isObject(this.mock)) {
                this.render();
            }
        },

        toJson: function () {
            console.log(this.collection.toJSON());
            var jsonTree = this.unFlatten(this.collection.toJSON());
            this.$(this.options.fieldId).val(JSON.stringify(jsonTree));
        },

        unFlatten: function (array, parent, tree) {
            var that = this,
                children,
                isChild;

            tree = typeof tree !== 'undefined' ? tree : [];

            if (typeof parent !== 'undefined') {
                isChild = function (child) {
                    return parent.id == child.parent;
                };
            } else {
                isChild = function (child) {
                    return !_.has(child, 'parent');
                };
            }

            children = _.filter(array, function (item) {
                return isChild(item);
            });

            if (!_.isEmpty(children)) {
                if (typeof parent === 'undefined') {
                    tree = children[0];
                } else {
                    parent['children'] = children
                }
                _.each(children, function (child) {
                    that.unFlatten(array, child)
                });

                return tree;
            }
        },

        addCondition: function (e) {
            e.preventDefault();

            var defaultCondition = {
                "target": "ticket",
                "condition": "neq",
                "property": "subject"
            };
            var model = this.collection.add(defaultCondition);
            var item = new ConditionItemView({"model": model, "collection": this.collection});
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
            } else {
                var item = new ConditionItemView({model: model, "collection": this.collection});
                parent.append(item.renderItemView().el);
            }

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
