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
                App.trigger('message:show',{ status: 'success', text:'Password successfully changed, you can use it to login'});
              })
              .fail(function(data, xhr){
                App.trigger('session:reset');
                App.alert({ title: 'Password Reset Failed', messages: ['Reset Code is invalid or expired'], xhr:xhr });
              });
          } else {
            this.model.reset(data)
              .done(function(model){
                App.alert({ title: 'Password Reset Info', status: 'info', messages: [{
                  status:'info',
                  text: 'We have sent you email to ' + model.get('email') + '.<br>' +
                  'Please click the link in that message to reset your password.'
                }] });
                App.trigger('session:login');
              })
              .fail(function(model, xhr){
                App.alert({ title: 'Password Reset Failed', xhr: xhr });
              });
          }
        });

        App.mainRegion.show(resetView);

      });

    };

  });

});