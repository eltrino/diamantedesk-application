define([
    'underscore',
    'diamanteautomation/js/app/models/automation-model',
    'diamanteautomation/js/app/views/automation-view',
    'diamanteautomation/js/app/views/automation-edit-view',
    'diamanteautomation/js/app/views/actions/automation-actions-collection-view',
    'diamanteautomation/js/app/views/groupings/automation-groupings-collection-view',
    'diamanteautomation/js/app/views/groupings/automation-groupings-view',
    'diamanteautomation/js/app/views/groupings/automation-groupings-edit-view',
    'oroui/js/app/components/base/component'
],function (_,
            AutomationModel,
            AutomationView,
            AutomationEditView,
            AutomationActionsCollectionView,
            AutomationGroupingsCollectionView,
            AutomationGroupingsView,
            AutomationGroupingsEditView,
            BaseComponent) {

    'use strict';

    var AutomationComponent = BaseComponent.extend({
        initialize: function (options) {
            this.processOptions(options);
            this.initView(options);
        },
        processOptions: function (options) {
            var type = options.type;
            this.el = options.el = options._sourceElement;
            delete options['_sourceElement'];
            delete options['type'];
            this.model = options.model =
                options.model ? new AutomationModel(_.extend(JSON.parse(options.model), {type: type }), options) :
                                new AutomationModel({type: type }, options);
        },
        initView: function (options) {
            if(options.edit){
                this.view = new AutomationEditView(options);
            } else {
                this.view = new AutomationView(options);
            }
            options = _.omit(options, 'el', 'model');
            new AutomationActionsCollectionView(_.extend( options,
                    { collection: this.model.get('actions') }
            ));
            if(options.edit){
                new AutomationGroupingsEditView(_.extend( options,
                    { model: this.model.get('grouping'), collectionView: AutomationGroupingsCollectionView }
                ));
            } else {
                new AutomationGroupingsView(_.extend( options,
                    { model: this.model.get('grouping'), collectionView: AutomationGroupingsCollectionView }
                ));
            }
        }
    });

    return AutomationComponent;
});
