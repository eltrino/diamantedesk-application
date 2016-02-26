define([
    'underscore',
    'oroui/js/app/views/base/collection-view'
],function (_, BaseCollectionView) {
    'use strict';

    var AbstractCollectionView = BaseCollectionView.extend({
        autoRender: true,
        itemView: {},
        editView: {},

        listen: {
            'add collection': 'update',
            'remove collection': 'update',
            'reset collection': 'update'
        },

        initialize : function(options){
            if(!this.options) {
                this.options = _.omit(options, 'collection');
            }
            BaseCollectionView.prototype.initialize.apply(this, arguments);
        },

        initItemView : function(model){
            if(this.options.edit){
                return new this.editView(_.extend(this.options, { model: model }));
            } else {
                return new this.itemView(_.extend(this.options, { model: model }));
            }
        },

        getTemplateData: function() {
            var data = BaseCollectionView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
        },

        addCollectionListeners : function() {
            this.listenTo(this.collection, 'parent:change', this.parentChanged);
            return BaseCollectionView.prototype.addCollectionListeners.apply(this, arguments);
        },

        update: function(){},
        parentChanged: function(){ }

    });

    return AbstractCollectionView;
});