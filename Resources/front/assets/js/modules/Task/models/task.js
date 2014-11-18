define(function(){

  App.module("Task.Models",function(Models, App, Backbone, Marionette, $, _){

    Models.TaskModel = Backbone.Model.extend({

      urlRoot: App.baseUrl + '/tasks',

      defaults: {
        subject : '',
        description: '',
        priority: 'normal'
      }

    });

    Models.TaskCollection = Backbone.Collection.extend({
      url: App.baseUrl+ '/tasks',
      model: Models.TaskModel
    });

    var API = {
      getTaskCollection: function() {
        var tasks = new Models.TaskCollection(),
            defer = $.Deferred();
        tasks.fetch({
          success: function(data){
            defer.resolve(data);
          }
        });
        return defer.promise();
      },
      getTaskModel: function(id) {
        var task = new Models.TaskModel({id:id});
            defer = $.Deferred();
        task.fetch({
          success: function(data){
            defer.resolve(data);
          },
          error: function(){
            defer.reject();
          }
        });
        return defer.promise();
      }
    };

    App.reqres.setHandler("task:collection", function(){
      return API.getTaskCollection();
    });

    App.reqres.setHandler("task:model", function(id){
      return API.getTaskModel(id);
    });

  });

});

