define(['app'], function(App){

  return App.module("Models",function(Models, App, Backbone, Marionette, $, _){

    var tasks;

    Models.Task = Backbone.Model.extend({});

    Models.TaskCollection = Backbone.Collection.extend({
      model: Models.Task
    });

    var initialize = function(){
      tasks = new Models.TaskCollection([
        { id: 1, created_at : "10-Jun-2014", subject : "Description #1", priority : "Hight", status : "Assigned"},
        { id: 2, created_at : "10-Jun-2014", subject : "Description #2", priority : "Hight", status : "Assigned"},
        { id: 3, created_at : "10-Jun-2014", subject : "Description #3", priority : "Hight", status : "Assigned"}
      ]);
    };

    var API = {
      getTaskEntities: function(){
        if(tasks === undefined){
          initialize();
        }
        return tasks;
      }
    };

    App.reqres.setHandler("task:model", function(){
        return API.getTaskEntities();
    });

  });


});

