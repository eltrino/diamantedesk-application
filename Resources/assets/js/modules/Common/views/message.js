define(['app', 'tpl!../templates/message.ejs'], function(App, massageTemplate){

  return App.module('Common.Message', function(Message, App, Backbone, Marionette, $, _){

    var _status = {
      'error' : {
        status_class : 'danger',
        status_icon : 'exclamation-circle'
      },
      'success' : {
        status_class : 'success',
        status_icon : 'check-circle'
      },
      'info' : {
        status_class : 'info',
        status_icon : 'info-circle'
      },
      'warning' : {
        status_class : 'warning',
        status_icon : 'exclamation-circle'
      }
    };

    Message.View = Marionette.ItemView.extend({
      template : massageTemplate,
      className: 'messages',

      constructor : function(options){
        var messages = new Backbone.Collection();
        if(_.isString(options)){
          messages.add({
            status : 'info',
            text : options
          });
        } else {
          messages.add(options);
        }
        Marionette.ItemView.call(this, { collection: messages } );
      },

      templateHelpers: function(){
        return {
          status_class : function(status){
            return _status[status].status_class;
          },
          status_icon : function(status){
            return _status[status].status_icon;
          }
        };
      },

      onShow: function(){
        this.$el.hide().slideDown(400)
          .delay(5000).slideUp(400, function(){
            this.destroy();
          }.bind(this));
      }

    });

  });

});