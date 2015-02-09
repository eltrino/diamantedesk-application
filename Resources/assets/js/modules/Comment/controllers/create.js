define(['app'], function(App){

  return App.module('Ticket.View.Comment.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.Controller = function(options){
      require([
        'Comment/models/comment',
        'Comment/views/form'], function(Models, Form){

        var commentModel = new Models.Model({},{ ticket : options.ticket }),
            commentCollection = options.collection,
            formView = new Form.ItemView({ model: commentModel });

        formView.on('form:submit', function(data){
          App.request('user:model:current').done(function(user){
            commentModel.set({
              'author': 'oro_' + user.get('id'),
              'authorFullName' : user.get('firstName') + ' ' + user.get('lastName')
            }, { 'silent': true });
            commentModel.save(data, {
              success : function(model){
                commentCollection.add(model);
                Create.Controller(options);
              },
              error : function(model, xhr){
                App.alert({
                  title: "Create Comment Error",
                  xhr : xhr
                });
              }
            });
          });
        });

        options.parentView.formRegion.show(formView);

      });
    };

  });

});