define(['app'], function(App){

  return App.module('Ticket.View.Comment.Form', function(Form, App, Backbone, Marionette, $, _){

    Form.Controller = function(options){
      require(['Comment/models/comment', 'Comment/views/form'], function(Models, Form){

        var FormView = new Form.ItemView({});

        FormView.on('form:submit', function(data){
          var CommentModel = new Models.Model({},{
                ticket : options.ticket
              });
          App.request("user:model:current").done(function(user){
            CommentModel.set({
              'author': 'oro_' + user.get('id'),
              'authorFullName' : user.get('firstName') + ' ' + user.get('lastName')
            }, { 'silent': true });
            CommentModel.save(data, {
              success : function(model){
                options.collection.add(model);
                FormView.clearForm();
              }
            });
          });
        });

        options.parentRegion.show(FormView);

      });
    };

  });

});