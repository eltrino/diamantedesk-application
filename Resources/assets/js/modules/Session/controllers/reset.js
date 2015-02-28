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
            this.model.newpassword(data);
          } else {
            this.model.reset(data);
          }
        });

        App.mainRegion.show(resetView);

      });

    };

  });

});