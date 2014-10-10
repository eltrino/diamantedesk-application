define(['app'], function(App){

  return App.module("Task.Models",function(Models, App, Backbone, Marionette, $, _){

    var tasks;

    Models.TaskModel = Backbone.Model.extend({});

    Models.TaskCollection = Backbone.Collection.extend({
      model: Models.TaskModel
    });

    var initialize = function(){
      tasks = new Models.TaskCollection([
        { id: 1, created_at : "10-Jun-2014", subject : "Description #1", priority : "Hight", status : "Assigned"},
        { id: 2, created_at : "10-Jun-2014", subject : "Description #2", priority : "Hight", status : "Assigned"},
        { id: 3, created_at : "10-Jun-2014", subject : "Description #3", priority : "Hight", status : "Assigned"},
        { id: 4, created_at : "10-Jun-2014", subject : "Description #4", priority : "Hight", status : "Assigned"},
        { id: 5, created_at : "10-Jun-2014", subject : "Description #5", priority : "Hight", status : "Assigned"},
        { id: 6, created_at : "10-Jun-2014", subject : "Description #6", priority : "Hight", status : "Assigned"},
        { id: 7, created_at : "10-Jun-2014", subject : "Description #7", priority : "Hight", status : "Assigned"}
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

    App.reqres.setHandler("task:collection", function(){
        return API.getTaskEntities();
    });

  });


});

