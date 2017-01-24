define(['app'], function(App){

  return App.module('Ticket.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.Controller = function(){

      App.mainRegion.showLoader();

      require([
        'Ticket/models/ticket',
        'Ticket/views/create',
        'Attachment/models/attachment'], function(Models, CreateView, AttachmentModel){

        var isSuccess = false,
            newTicketModel = new Models.Model(),
            attachmentCollection = new AttachmentModel.Collection(),
            newTicketView = new Create.ItemView({
              model: newTicketModel,
              attachmentCollection : attachmentCollection
            }),
            modalCreateView = new Create.ModalView({
              title: 'Add New Ticket',
              submit: 'Submit'
            });

        modalCreateView.on('show', function(){
          this.$el.modal({backdrop: 'static'});
        });

        modalCreateView.on('modal:closed', function(){
          if(!isSuccess){
            App.back();
          }
        });

        newTicketView.on('form:submit', function(attr){
          App.request('user:model:current').done(function(user){
            attr.reporter =  'diamante_' + user.get('id');
            if(attachmentCollection.length){
              attachmentCollection.forEach(function(model){ model.unset('base64'); });
              attr.attachmentsInput = attachmentCollection.toJSON();
            }
            newTicketModel.save(attr, {
              success : function(resultModel){
                isSuccess = true;
                App.trigger('ticket:view', resultModel.get('key'));
                App.trigger('message:show', {
                  status:'success',
                  text: 'Ticket ' + resultModel.get('key') + ' created'
                });
                modalCreateView.$el.modal('hide');
              },
              error : function(){
                App.alert({
                  title: __('diamante_front.ticket.controller.alert.create_error.title')
                });
              }
            });
          });
        });

        newTicketView.on('attachment:add', function(data){
          attachmentCollection.add(data);
        });
        newTicketView.on('attachment:delete', function(model){
          attachmentCollection.remove(model);
        });

        App.debug('log', App.mainRegion.hasView());
        App.dialogRegion.show(modalCreateView);
        modalCreateView.modalBody.show(newTicketView);

      });

    };

  });

});
