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
        initialize: function () {
            this.mock = Mock.data;
            this.collection = new ActionCollection();

            if (_.isObject(this.mock)) {
                this.render();
            }
        },

        addAction: function () {
            var view = new ActionItemView();
            this.$('#list-action').append(view.renderEdit().el);
        },

        render: function() {
            var that = this;

            _.each(this.mock, function(item) {
                var model = new ActionModel(item);
                that.collection.add(model);
                var view = new ActionItemView({"model": model, "collection": that.collection});
                that.$('#list-action').append(view.renderItemView().el);
            });
        }
    });
});
