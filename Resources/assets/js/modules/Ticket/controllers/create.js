define(['app'], function(App){

  return App.module('Ticket.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.Controller = function(){

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
              title: 'Add New Ticket'
            });

        modalCreateView.on('show', function(){
          this.$el.modal();
        });

        modalCreateView.on('modal:closed', function(){
          if(!isSuccess){
            App.back();
          }
        });

        newTicketView.on('form:submit', function(attr){
          App.request('user:model:current').done(function(user){
            attr.reporter =  'diamante_' + user.get('id');
            newTicketModel.save(attr, {
              success : function(resultModel){
                if(attachmentCollection.length){
                  attachmentCollection.save({
                    ticket : resultModel,
                    success : function(){
                      isSuccess = true;
                      App.trigger('ticket:view', resultModel.get('id'));
                      modalCreateView.$el.modal('hide');
                    }
                  });
                } else {
                  isSuccess = true;
                  App.trigger('ticket:view', resultModel.get('id'));
                  modalCreateView.$el.modal('hide');
                }
              },
              error : function(){
                App.alert({
                  title: "Create Ticket Error"
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
