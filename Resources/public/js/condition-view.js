define(['underscore', 'backbone', './condition-collection', './condition-model', './rule-dialog-view'
    ], function(_, Backbone, ConditionCollection, ConditionModel, RuleDialog) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        el: '#rule-conditions',
        ruleDialogView: _.template($('#rule-dialog-view').html()),

        /**
         * Constructor
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.collection = new ConditionCollection();
            //this._prepareCollections();

            $('#add-rule-condition').on('click', _.bind(this._addCondition, this));
            $('#add-rule-group').on('click', _.bind(this._addGroup, this));
            this.listenTo(this.getCollection(), 'add', this.onCollectionChange);
        },

        /**
         * Get collection object
         *
         * @returns {*}
         */
        getCollection: function() {
            return this.collection;
        },

        _addCondition: function() {
            var dialog = new RuleDialog({'collection': this.collection, 'model': new ConditionModel()});
            dialog.open();
        },

        onCollectionChange: function() {
            this.$el.append('collection change');
        },

        _addGroup: function() {
            console.log('group add');
        },

        /**
         * Fill data to collections from hidden inputs
         *
         * @returns {*}
         * @private
         */
        _prepareCollections: function() {
            var rule = $.parseJSON($(this.options.fieldId).val());

            this.getCollection().reset(rule);
            return this;
        }
    });
});
