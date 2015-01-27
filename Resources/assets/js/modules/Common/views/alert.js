define(['app', 'tpl!../templates/alert.ejs'], function(App, alertTemplate){

  return App.module('Common.Modal', function(Alert, App, Backbone, Marionette, $, _){

    var body = $('body');

    Alert.View = Marionette.ItemView.extend({
      className: 'modal fade',
      template: alertTemplate,

      initialize: function(options){
        var opt = options || {};
        this.title = opt.title || 'Error';
        this.messages = opt.messages || ['An unprocessed error happened. Please try again!'];
      },

      templateHelpers: function(){
        return {
          title: this.title,
          messages: this.messages
        };
      },

      events: {
        'show.bs.modal': 'beforeShowModal',
        'hidden.bs.modal': 'hideModal'
      },

      beforeShowModal: function(){
        body.addClass('blured');
      },

      hideModal: function(){
        body.removeClass('blured');
        this.destroy();
      },

      onShow: function(){
        this.$el.modal();
      }

    });

  });

});