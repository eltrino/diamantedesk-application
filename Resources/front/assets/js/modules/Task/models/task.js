define(['app'], function(App){

  return App.module("Task.Models",function(Models, App, Backbone, Marionette, $, _){

    var tasks;

    Models.TaskModel = Backbone.Model.extend({});

    Models.TaskCollection = Backbone.Collection.extend({
      model: Models.TaskModel
    });

    var initialize = function(){
      // Temporary
      return $.get('assets/js/modules/Task/models/tasks.json', function(json){
        tasks = new Models.TaskCollection(json);
      });

    };

    var API = {
      getTaskEntities: function(){
        var defer = $.Deferred();
        if(tasks === undefined){
          initialize().done(function(){
            defer.resolve(tasks)
          });
        } else {
          defer.resolve(tasks)
        }
        return defer.promise();
      }
    };

    App.reqres.setHandler("task:collection", function(){
        return API.getTaskEntities();
    });

  });


});

