define(['underscore', 'backbone', './helper/helper'
], function (_, Backbone, Helper) {
    'use strict';

    var $ = Backbone.$;

    return Backbone.View.extend({
        el: 'body',
        dialogEl: '.ui-dialog',
        targetTypes: '.target-types',
        targetProperties: '.target-properties',
        ruleDialogView: _.template($('#rule-dialog-view').html()),
        targetTypesView: _.template($('#target-types-view').html()),
        targetPropertiesView: _.template($('#target-properties-view').html()),
        events: {
            'click .ui-dialog-titlebar-close, .automation-dialog-cancel': '_close',
            'click .automation-dialog-add': '_add',
            'change .diamante-select-conditions': function (e) {
                this.getModel().trigger('change:type', this.getModel(), $(e.target).val());
            },
            'change .diamante-select-target-types': function(e) {
                this.getModel().trigger('change:target', this.getModel(), $(e.target).val());
            }
        },

        /**
         * Constructor
         */
        initialize: function (options) {
            this.options = _.defaults(options || {}, this.options);
            this.conditions = Helper.conditions;
            this.targets = Helper.targets;

            this.listenTo(this.getModel(), 'change:type', this._onTypeChange);
            this.listenTo(this.getModel(), 'change:target', this._onTargetChange);
        },

        /**
         * Get collection object
         *
         * @returns {*}
         */
        getCollection: function () {
            return this.options.collection;
        },

        getModel: function () {
            return this.options.model;
        },

        open: function () {
            this.$el.append(this._render());
            var defaultType = _.first(_.keys(this.conditions));
            var defaultTarget = _.first(this.conditions[defaultType].support);
            this.options.model.set({'type': defaultType, 'target': defaultTarget});
        },

        _render: function () {
            return this.ruleDialogView({'conditions': this.conditions});
        },

        _close: function () {
            this.$(this.dialogEl).remove();
        },

        _add: function () {
            this.getCollection().add(this.getModel());
            this._close();
        },

        _onTypeChange: function (model, name) {
            var template = this.targetTypesView({'types': this.conditions[name].support});
            $(this.targetTypes).html(template);
        },

        _onTargetChange: function (model, name) {
            var template = this.targetPropertiesView({'targets': this.targets[name]});

            $(this.targetProperties).html(template);
        }
    });
});
