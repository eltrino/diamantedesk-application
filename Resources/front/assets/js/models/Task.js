define(['app'], function(App){

  App.module("Models",function(Entities, App, Backbone, Marionette, $, _){

    var tasks;

    Entities.Task = Backbone.Model.extend({});

    Entities.TaskCollection = Backbone.Collection.extend({
      model: Entities.Task
    });

    var initialize = function(){
      tasks = new Entities.TaskCollection([
        { id: 1, label : "Label #1", description : "Description #1"},
        { id: 2, label : "Label #2", description : "Description #2"},
        { id: 3, label : "Label #3", description : "Description #3"}
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

  return {};
});

