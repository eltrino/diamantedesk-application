define(['app'], function(App){

  return App.module('Ticket.View.Comment.Form', function(Form, App, Backbone, Marionette, $, _){

    Form.Controller = function(options){
      require(['Comment/models/comment', 'Comment/views/form'], function(Models, Form){

        var formView = new Form.ItemView({});

        formView.on('form:submit', function(data){
          var commentModel = new Models.Model({},{
                ticket : options.ticket
              });
          App.request('user:model:current').done(function(user){
            commentModel.set({
              'author': 'oro_' + user.get('id'),
              'authorFullName' : user.get('firstName') + ' ' + user.get('lastName')
            }, { 'silent': true });
            commentModel.save(data, {
              success : function(model){
                options.collection.add(model);
                formView.clearForm();
              }
            });
          });
        });

        options.parentRegion.show(formView);

      });
    };

  });

});