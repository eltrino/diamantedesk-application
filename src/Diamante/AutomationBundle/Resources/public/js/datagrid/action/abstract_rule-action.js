/*global define*/
define([
    'oro/datagrid/action/mass-action'
], function (MassAction) {
    'use strict';

    var AbstractRuleAction;

    AbstractRuleAction = MassAction.extend({
        getConfirmDialog: function(options) {
            AbstractRuleAction.__super__.getConfirmDialog.apply(this, arguments);
            this.confirmModal.$el.addClass('diam-modal-as-dialog');
            return this.confirmModal;
        }
    });

    return AbstractRuleAction;
});