define(['oroui/js/app/components/base/component'],function (BaseComponent) {
    'use strict';

    var AutomationComponent = BaseComponent.extend({
        initialize: function (options) {
            console.log('AutomationComponent is initialized', options);
        }
    });

    return AutomationComponent;
});