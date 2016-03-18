define([
    'underscore',
    'oroui/js/mediator',
    'tpl!diamanteautomation/js/app/templates/automation-edit-template.ejs',
    'diamanteautomation/js/app/views/automation-view'
],function (_, mediator , AutomationEditTemplate, AutomationView) {
    'use strict';

    var AutomationEditView = AutomationView.extend({
        template : AutomationEditTemplate,

        events: {
            'change > .control-group :input' : 'change'
        },

        listen: {
            'change:target model': 'updateTarget'
        },

        change: function(e) {
            var input = this.$(e.target);
            this.model.set( input.data('attr'), input.val());
        },

        updateTarget: function(model, attr){
            this.model.get('actions').trigger('parent:change', attr);
            this.model.get('grouping').trigger('parent:change', attr);
        },

        render: function(){
            AutomationView.prototype.render.apply(this, arguments);
            mediator.execute('layout:init', this.$el)
        }
    });

    return AutomationEditView;
});