define(['app', 'tpl!../templates/alert.ejs'], function(App, alertTemplate){

  return App.module('Common.Alert', function(Alert, App, Backbone, Marionette, $, _){

    var body = $('body');

    Alert.View = Marionette.ItemView.extend({
      className: 'modal modal-alert fade',
      template: alertTemplate,

      initialize: function(options){
        var opt = options || {},
            message;
        this.title = opt.title || __('diamante_front.common.view.alert.title');
        this.status = opt.status || 'error';
        this.messages = opt.messages || __('diamante_front.common.view.alert.text');
        if(opt.xhr && opt.xhr.responseJSON){
          message = opt.xhr.responseJSON.message || opt.xhr.responseJSON.error;
        }
        if(message) {
          this.messages = message;
        }
        if(_.isString(this.messages)){
          this.messages = [ this.messages ];
        }
      },

      templateHelpers: function(){
        var messages = _.map(this.messages, function(message){
          if(_.isObject(message) && message.status){
            switch (message.status){
              case 'error':
                message.status_class = 'danger';
                message.status_icon = 'exclamation-circle';
                break;
              case 'success':
                message.status_class = 'success';
                message.status_icon = 'check-circle';
                break;
              case 'info':
                message.status_class = 'info';
                message.status_icon = 'info-circle';
                break;
              case 'warning':
                message.status_class = 'warning';
                message.status_icon = 'exclamation-circle';
                break;
            }
            message.text = message.text.replace(/\n/g, '<br />');
            return message;
          } else {
            return {
              status_class: 'danger',
              status_icon: 'exclamation-circle',
              text: message.replace(/\n/g, '<br />')
            };
          }
        });
        return {
          title: this.title,
          status: this.status,
          messages: messages
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

      onDestroy: function(){
        body.removeClass('blured');
        $('.modal-backdrop').remove();
      },

      onShow: function(){
        this.$el.modal();
      }

    });

  });

});