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
            this.model = options.model =
                options.model ? new AutomationModel(options.model) : new AutomationModel({type: type });
        },
        initView: function (options) {
            this.view = new AutomationView(options);
            options = _.omit(options, 'el', 'model');
            require([
                'diamanteautomation/js/app/views/actions/automation-actions-collection-view'
            ],function(ActionsCollectionView){
                new ActionsCollectionView(
                    _.extend( options,
                        { collection: this.model.get('actions') }
                    )
                );
            }.bind(this));
            require([
                'diamanteautomation/js/app/views/conditions/automation-conditions-edit-view'
            ],function(ConditionsView){
                new ConditionsView(_.extend( options,
                    { model: this.model.get('conditions') }
                ));
            }.bind(this));
        }
    });

    return AutomationComponent;
});
