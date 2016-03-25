define(function(require) {
    'use strict';

    var HeaderDropdownPlugin;
    var $ = require('jquery');
    var _ = require('underscore');
    var BasePlugin = require('oroui/js/app/plugins/base/plugin');
    var Backbone = require('backbone');
    var mediator = require('oroui/js/mediator');
    var layout = require('oroui/js/layout');
    var tools = require('oroui/js/tools');

    HeaderDropdownPlugin = BasePlugin.extend({
        initialize: function(grid) {
            this.grid = grid;
            this.grid.on('shown', _.bind(this.onGridShown, this));
        },

        onGridShown: function() {
            if (this.enabled && !this.connected) {
                this.enable();
            }
        },

        enable: function() {
            if (!this.grid.rendered) {
                HeaderDropdownPlugin.__super__.enable.call(this);
                return;
            }

            this.setupCache();
            this.$grid.on('click.fixDropdown', 'thead:first .dropdown', _.bind(function() {
                this.fixDropdown.apply(this, arguments);
            }, this));
            this.connected = true;
            HeaderDropdownPlugin.__super__.enable.call(this);
        },

        disable: function() {
            this.connected = false;
            this.$grid.off('click.fixDropdown');
            this.domCache.body.off('click.fixDropdown');
            HeaderDropdownPlugin.__super__.disable.call(this);
        },

        setupCache: function() {
            this.$grid = this.grid.$grid;
            this.$el = this.grid.$el;
            this.documentHeight = $(document).height();
            this.domCache = {
                body: $(document.body),
                gridContainer: this.$grid.parent(),
                otherScrollContainer: this.$grid.parents('.other-scroll-container:first'),
                gridScrollableContainer: this.$grid.parents('.grid-scrollable-container:first'),
                thead: this.$grid.find('thead:first'),
            };
        },

        fixDropdown : function(e){
            var dropdown = $(e.currentTarget),
                thead = this.domCache.thead,
                gridScrollableContainer = this.domCache.gridScrollableContainer,
                otherScrollContainer = this.domCache.otherScrollContainer,
                dropdownElement;
            if(!dropdown.hasClass('open')){
                dropdownElement = $('.dropdown-menu', dropdown);
                if(gridScrollableContainer.height() < dropdownElement.height() + thead.height()){
                    gridScrollableContainer.css('overflow', 'visible');
                    otherScrollContainer.css('overflow', 'visible');
                    this.domCache.body.on('click.fixDropdown', function(e){
                        var target = $(e.target);
                        if((dropdown == target || dropdown.has(target).length) && dropdown.hasClass('open') || !target.closest('.dropdown').length) {
                            gridScrollableContainer.css('overflow', '');
                            otherScrollContainer.css('overflow', '');
                        }
                    })
                }
            }
        }
    });

    return HeaderDropdownPlugin;
});
