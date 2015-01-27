define(['app'], function(App){

  return App.module('Ticket.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.Controller = function(id){

      require([
        'Ticket/models/ticket',
        'Ticket/views/edit'], function(Models, EditView){

        App.request('ticket:model', id).done(function(editTicketModel){

          var editTicketView = new Edit.ItemView({
                model: editTicketModel
              }),
              modalEditView = new Edit.ModalView({
                title: 'Edit Ticket ' + editTicketModel.get('key')
              });

          modalEditView.on('show', function(){
            this.$el.modal();
          });

          modalEditView.on('modal:closed', function(){
            App.trigger('ticket:view', editTicketModel.get('id'));
          });

          editTicketView.on('form:submit', function(data){
            this.model.set(data);
            var attrs = this.model.changedAttributes();
            if(attrs){
              this.model.save(attrs,{
                patch : true,
                success : function(resultModel){
                  App.trigger('ticket:view', resultModel.get('id'));
                  modalEditView.off('modal:closed');
                  modalEditView.$el.modal('hide');
                },
                error : function(){
                  App.alert({
                    title: "Edit Ticket Error"
                  });
                }
              });
            } else {
              modalEditView.$el.modal('hide');
            }
          });

          App.debug('log', App.mainRegion.hasView());
          App.dialogRegion.show(modalEditView);
          modalEditView.modalBody.show(editTicketView);

        }).fail(function(){

          var ticketMissingView = new Edit.MissingView();
          App.mainRegion.show(ticketMissingView);

        });

      });

    };

  });

});