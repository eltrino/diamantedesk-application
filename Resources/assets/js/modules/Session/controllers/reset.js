define(['app'], function(App){

  return App.module('Session', function(Session, App, Backbone, Marionette, $, _){

    Session.ResetController = function(){

      require(['modules/Session/views/reset'], function(){

        var resetView = new Session.ResetView({
          model : App.session
        });

        resetView.on('form:submit', function(data){
          this.model.set(data);
        });

        App.mainRegion.show(resetView);

      });

    };

  });

});