define(['app', 'helpers/wsse'], function(App, Wsse){

  return App.module('User.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.Controller = function(options){

      require([
        'User/models/user',
        'User/views/edit'], function(){

        var request = App.request('user:model:current'),
            modalEditView = new Edit.ModalView({
              title: 'Edit User',
              submit: 'Submit'
            });

        modalEditView.on('show', function(){
          this.$el.modal();
        });

        modalEditView.on('modal:closed', function(){
            App.back();
        });

        request.done(function(userModel){
          var userEditView = new Edit.ItemView({
            model: userModel
          });

          userEditView.on('form:submit', function(data){
            var ignore = [],
                isChanged = false;
            if(!data.password){
              delete data.password;
              ignore = ['password'];
            }
            if(this.model.set(data, {ignore: ignore, validate: true})){
              if(data.password) {
                data.password = Wsse.encodePassword(data.password);
              }
              isChanged = this.model.hasChanged();
              this.model.save(data,{
                ignore: ignore,
                patch: true,
                success : function(){
                  if(data.password){
                    App.session.update({ password : data.password });
                  }
                  if(isChanged || data.password){
                    App.trigger('message:show', {
                      status: 'success',
                      text: __('diamante_front.user.controller.message.update_success')
                    });
                  }
                  modalEditView.$el.modal('hide');
                }.bind(this),
                error : function(model, xhr){
                  App.alert({
                    title: __('diamante_front.user.controller.alert.edit_error.title'),
                    xhr : xhr
                  });
                }
              });
            }
          });

          App.dialogRegion.show(modalEditView);
          modalEditView.modalBody.show(userEditView);

        });

      });

    };

  });

});
