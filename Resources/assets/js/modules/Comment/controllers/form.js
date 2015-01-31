define(['app'], function(App){

  return App.module('Ticket.View.Comment.Form', function(Form, App, Backbone, Marionette, $, _){

    Form.Controller = function(model, options){
      require(['Comment/models/comment', 'Comment/views/form'], function(Models, Form){

        var commentModel = model || new Models.Model({},{ ticket : options.ticket }),
            formView = new Form.ItemView({ model: commentModel });

        formView.on('form:submit', function(data){
          if(model) {
            commentModel.save(data,{
              wait: true,
              success : function(){
                Form.Controller(null, options);
              },
              error : function(model, xhr){
                App.alert({
                  title: "Create Comment Error",
                  xhr : xhr
                });
              }
            });
          } else {
            App.request('user:model:current').done(function(user){
              commentModel.set({
                'author': 'oro_' + user.get('id'),
                'authorFullName' : user.get('firstName') + ' ' + user.get('lastName')
              }, { 'silent': true });
              commentModel.save(data, {
                success : function(model){
                  options.collection.add(model);
                  formView.clearForm();
                },
                error : function(model, xhr){
                  App.alert({
                    title: "Create Comment Error",
                    xhr : xhr
                  });
                }
              });
            });
          }
        });

        options.parentRegion.show(formView);

      });
    };

  });

});