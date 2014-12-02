define(['app','config'], function(App, Config){

  return App.module("Task", function(Task, App, Backbone, Marionette, $, _){

    Task.TaskModel = Backbone.Model.extend({
      url: function(){
        return Config.baseUrl + '/tasks/'+this.id+'.json';
      },

      defaults: {
        subject : '',
        description: '',
        priority: 'normal'
      }

    });

    Task.TaskCollection = Backbone.Collection.extend({
      url: Config.baseUrl+ '/tasks.json',
      model: Task.TaskModel
    });

    var API = {
      getTaskCollection: function() {
        var tasks = new Task.TaskCollection(),
            defer = $.Deferred();
        tasks.fetch({
          success: function(data){
            defer.resolve(data);
          }
        });
        return defer.promise();
      },
      getTaskModel: function(id) {
        var task = new Task.TaskModel({id:id}),
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

