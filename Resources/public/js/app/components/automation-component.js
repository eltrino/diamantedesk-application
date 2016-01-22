define([
    'diamanteautomation/js/app/models/automation-model',
    'diamanteautomation/js/app/views/automation-view',
    'oroui/js/app/components/base/component'
],function (AutomationModel,  AutomationView, BaseComponent) {
    'use strict';

    var AutomationComponent = BaseComponent.extend({
        initialize: function (options) {
            this.processOptions(options);
            this.initView(options);
        },
        processOptions: function (options) {
            var type = options.type;
            options.el = options._sourceElement;
            delete options['_sourceElement'];
            delete options['type'];
            console.log(options.config);
            options.model = options.model ? new AutomationModel(options.model, options) : new AutomationModel({type: type }, options);
        },
        initView: function (options) {
            this.view = new AutomationView(options);
            require(['diamanteautomation/js/app/views/actions/automation-actions-edit-view'],function(ActionsView){
                new ActionsView(_.extend(
                    _.omit(options, 'el'),
                    { model: options.model.get('actions') }
                ));
            });
            require(['diamanteautomation/js/app/views/conditions/automation-conditions-edit-view'],function(ConditionsView){
                new ConditionsView(_.extend(
                    _.omit(options, 'el'),
                    { model: options.model.get('conditions') }
                ));
            });
        }
    });

    return AutomationComponent;
});
