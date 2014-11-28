define(['app'], function(App) {

  return App.module('SessionManager.Models', function(Models, App, Backbone, Marionette, $, _){

    Models.SessionModel = Backbone.Model.extend({

      urlRoot: '../api/',

      initialize: function () {
        $.ajaxPrefilter(function( options, originalOptions, jqXHR) {
          options.xhrFields = {
            withCredentials: true
          };
        });
      },

      login: function(creds) {
        var that = this;
        this.save(creds, {
          success: function (model, resp) {
            if (resp.success == false) {
              alert(resp.message);
            }
            that.unset('password');
            that.set(resp.data);
          }
        });
      },

      logout: function() {
        var that = this;
        this.destroy({
          success: function (model, resp) {
            model.clear({silent:true});
            that.set({logged_in: false});
          }
        });
      },

      getAuth: function(callback) {
        this.fetch({
          success: callback
        });
      }
    });
  });
});