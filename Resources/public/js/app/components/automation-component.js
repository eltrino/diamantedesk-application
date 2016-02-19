define([
    'diamanteautomation/js/app/models/automation-model',
    'diamanteautomation/js/app/views/automation-view',
    'diamanteautomation/js/app/views/automation-edit-view',
    'diamanteautomation/js/app/views/actions/automation-actions-collection-view',
    'diamanteautomation/js/app/views/groupings/automation-groupings-collection-view',
    'diamanteautomation/js/app/views/groupings/automation-groupings-edit-view',
    'oroui/js/app/components/base/component'
],function (AutomationModel,
            AutomationView,
            AutomationEditView,
            AutomationActionsCollectionView,
            AutomationGroupingsCollectionView,
            AutomationGroupingsEditView,
            BaseComponent) {

    'use strict';

    var AutomationComponent = BaseComponent.extend({
        initialize: function (options) {
            console.log(options.model);
            console.log(options.config);
            this.processOptions(options);
            this.initView(options);
            if(options.edit){
                this.el.parents('form').on('submit', function(){
                    this.el.find('input[name="diamante_automation_update_rule_form[rule]"]').val(JSON.stringify(this.model.serializePlain()));
                }.bind(this));
            }
        },
        processOptions: function (options) {
            var type = options.type;
            this.el = options.el = options._sourceElement;
            delete options['_sourceElement'];
            delete options['type'];
            this.model = options.model =
                options.model ? new AutomationModel(options.model) : new AutomationModel({type: type });
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
            new AutomationGroupingsEditView(_.extend( options,
                { model: this.model.get('grouping'), collectionView: AutomationGroupingsCollectionView }
            ));
        }
    });

    return AutomationComponent;
});
