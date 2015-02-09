define(['app'], function(App){

  return App.module('Ticket.View.Comment.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.Controller = function(commentModel, options){

      require(['Comment/views/form'], function(Form){

        var parentView = options.parentView,
            formView = new Form.ItemView({ model: commentModel });

        formView.on('form:submit', function(data){
          commentModel.save(data,{
            wait: true,
            success : function(){
              require(['Comment/controllers/create'],function(Create){
                Create.Controller(options);
              });
            },
            error : function(model, xhr){
              App.alert({
                title: "Create Comment Error",
                xhr : xhr
              });
            }
          });
        });

        parentView.formRegion.show(formView);

      });
    };

  });

});