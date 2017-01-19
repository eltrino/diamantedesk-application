define(['app'], function(App){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.ResetController = function(hash){

      require(['modules/Session/views/reset'], function(){

        if(hash){
          App.session.set('hash', hash);
        }

        var resetView = new Session.ResetView({
          model : App.session
        });

        resetView.on('form:submit', function(data){
          if(hash){
            this.model.newPassword(data)
              .done(function(){
                App.trigger('session:login');
                App.trigger('message:show',{
                  status: 'success',
                  text:__('diamante_front.session.controller.message.reset_success')
                });
              })
              .fail(function(data, xhr){
                App.trigger('session:reset');
                App.alert({
                  title: __('diamante_front.session.controller.alert.reset_fail.title'),
                  messages: [__('diamante_front.session.controller.alert.reset_fail.text')],
                  xhr:xhr
                });
              });
          } else {
            this.model.reset(data)
              .done(function(model){
                App.alert({
                  title: 'Password Reset Info',
                  status: 'info',
                  messages: [{
                    status:'info',
                    text: __('diamante_front.session.controller.alert.reset_info.text', {email: model.get(email)})
                  }]
                });
                App.trigger('session:login');
              })
              .fail(function(model, xhr){
                App.alert({
                  title: __('diamante_front.session.controller.alert.reset_fail.title'),
                  xhr: xhr
                });
              });
          }
        });

        App.mainRegion.show(resetView);

      });

    };

  });

});