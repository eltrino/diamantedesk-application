define([
    'jquery',
    'underscore',
    'tpl!diamanteautomation/js/app/templates/conditions/automation-conditions-edit-template.ejs',
    'oroui/js/app/views/base/view'
],function ($, _, AutomationConditionsEditTemplate, BaseView) {
    'use strict';

    var AutomationConditionsEditView = BaseView.extend({
        autoRender: true,
        template : AutomationConditionsEditTemplate,

        listen: {
            'change model': 'render'
        },

        events: {
            'click button[data-action="delete"]': 'removeItem'
        },

        initialize: function(options){
            this.options = _.omit(options, 'model');
            this.delegate('change', ':input', this.change);
            BaseView.prototype.initialize.apply(this, arguments);
        },

        getTemplateData: function() {
            var data = BaseView.prototype.getTemplateData.call(this),
                def = _.clone(this.model.defaults);
            for(var key in data) {
                data[key] = data[key];
            }
            return _.extend(def, data, this.options);
        },

        render: function () {
            AutomationConditionsEditView.__super__.render.apply(this, arguments);
            this.$(':input:not(button)').trigger('change');
            if(this.model.collection.length == 1){
                this.$('button[data-action="delete"]').hide();
            }
            return this;
        },

        change: function (e) {
            var input = $(e.target);
            this.model.set(input.data('attr'), input.val() );
        },

        removeItem: function(){
            this.model.destroy();
        }

    });

    return AutomationConditionsEditView;
});