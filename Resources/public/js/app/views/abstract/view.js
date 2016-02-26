define([
    'underscore',
    'oroui/js/app/views/base/view'
],function (_, BaseView) {
    'use strict';

    var AbstractView = BaseView.extend({
        autoRender: true,

        initialize: function(options){
            this.options = _.omit(options, 'el', 'model');
        },

        getTemplateData: function() {
            var data = BaseView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
        }
    });

    return AbstractView;
});