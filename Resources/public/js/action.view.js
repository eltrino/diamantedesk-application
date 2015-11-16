define(['underscore',
    'backbone',
    './action.item.view',
    './action.collection',
    './action.model',
    './actions.mock'
], function (_,
             Backbone,
             ActionItemView,
             ActionCollection,
             ActionModel,
             Mock) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        el: '#action-block',

        events: {
            'click #add-rule-action': 'addAction'
        },

        /**
         * Constructor
         */
        initialize: function (options) {
            this.options = options;
            this.collection = new ActionCollection();

            this.listenTo(this.collection, 'toJson', this.toJson);
            this.listenTo(this.collection, 'addNew', this.renderNew);

            if (!_.isEmpty(options.actions)) {
                this.actions = JSON.parse(options.actions);
                this.renderStored();
            }
        },

        toJson: function () {
            if (this.options.actionsEl) {
                var json = this.collection.toJSON();
                if (_.isEmpty(json)) {
                    json = undefined;
                }
                this.$(this.options.actionsEl).val(JSON.stringify(json));
            }
        },

        addAction: function (e) {
            e.preventDefault();

            var defaultAction = {
                "action": "notifyByEmail",
                "property": "recipients",
                "value": "admin@mail.com"
            };
            this.collection.add(defaultAction);
            this.collection.trigger('addNew');
        },

        render: function(actions, getModel) {
            var that = this,
                parent = this.$('#list-action');

            _.each(actions, function(item) {
                var model = getModel(item);
                var view = new ActionItemView({"model": model, "collection": that.collection});
                parent.append(view.renderItemView().el);
            });

            this.collection.trigger("toJson");
        },

        renderStored: function () {
            var that = this;

            this.render(this.actions, function (data) {
                return that.collection.add(data, {"silent": true});
            });
        },

        renderNew: function () {
            this.$('#list-action').html('');
            var jsonTree = this.collection.toJSON(),
                that = this;

            this.render(jsonTree, function (data) {
                return that.collection.add(data, {"silent": true});
            });
        }
    });
});
