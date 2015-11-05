define(['underscore',
    'backbone',
    './condition.collection',
    './condition.model',
    './condition.item.view',
    './condition.group.view'
], function (_,
             Backbone,
             ConditionCollection,
             ConditionModel,
             ConditionItemView,
             ConditionGroupView) {
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
            this.collection = new ConditionCollection();

            this.listenTo(this.collection, 'toJson', this.toJson);
            this.listenTo(this.collection, 'addNew', this.renderNew);

            if (!_.isEmpty(options.conditions)) {
                this.conditions = JSON.parse(options.conditions);
                this.renderStored();
            }
        },

        toJson: function () {
            if (this.options.conditionsEl) {
                var jsonTree = this.unFlatten(this.collection.toJSON());
                this.$(this.options.conditionsEl).val(JSON.stringify(jsonTree));
            }
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
            this.collection.add(defaultCondition);
            this.collection.trigger('addNew');
        },

        addGroup: function (e) {
            e.preventDefault();

            var defaultGroup = {
                "expression": "AND",
                "target": "ticket"
            };
            this.collection.add(defaultGroup);
            this.collection.trigger('addNew');
        },

        renderStored: function () {
            var parent = this.$('#list-condition');
            var that = this;
            this.conditions.isParent = true;

            this.build(this.conditions, parent, function (data) {
                data = that.getAttributes(data);
                return that.collection.add(data, {"silent": true});
            });

            this.collection.trigger("toJson");
        },

        renderNew: function () {
            this.$('#list-condition').html('');
            var jsonTree = this.unFlatten(this.collection.toJSON());
            var parent = this.$('#list-condition');
            var that = this;

            this.build(jsonTree, parent, function (data) {
                return that.collection.get(data.id);
            });

            this.collection.trigger("toJson");
        },

        build: function (mock, parent, getModel) {
            var model = getModel(mock),
                isGroup = _.has(mock, 'expression'),
                hasChildren = !_.isEmpty(mock.children);

            parent = this.render(isGroup, parent, model);

            if (hasChildren) {
                var that = this;
                _.each(mock.children, function (item) {
                    that.build(item, parent, getModel);
                });
            }
        },

        render: function (isGroup, parent, model) {
            var group;

            if (isGroup) {
                group = new ConditionGroupView({model: model, "collection": this.collection});
                parent.append(group.renderItemView().el);
                parent = group.$el;
            } else {
                var item = new ConditionItemView({model: model, "collection": this.collection});
                parent.append(item.renderItemView().el);
            }

            return parent;
        },

        getAttributes: function (data) {
            var whiteList = [
                'id',
                'weight',
                'target',
                'active',
                'expression',
                'property',
                'value',
                'parent',
                'condition',
                'isParent'
            ];

            return _.pick(data, whiteList);
        }
    });
});
