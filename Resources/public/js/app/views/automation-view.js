define(['oroui/js/app/views/base/view'],function (BaseView) {
    'use strict';

    var AutomationView = BaseView.extend({
        autoRender: true,

        render: function () {
            console.log(this.options);
            console.log(this);
            return AutomationView.__super__.render.call(this);
        },

        dispose: function () {
            if (this.disposed) {
                return;
            }
            AutomationView.__super__.dispose.call(this);
        }
    });

    return AutomationView;
});