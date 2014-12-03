define(['app'], function(App){

  return App.module('Ticket.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.TicketController = function(){

      require(['modules/Ticket/models/ticket', 'modules/Ticket/views/create'], function(Models, CreateView){

        var isSuccess = false,
            newTicketModel = new Models.TicketModel(),
            newTicketView = new Create.ItemView({
              model: newTicketModel
            }),
            modalCreateView = new Create.ModalView({
              title: 'Add New Ticket'
            });

        modalCreateView.on('show', function(){
          this.$el.modal();
        });

        modalCreateView.on('modal:closed', function(){
          if(!isSuccess){
            Backbone.history.history.back();
          }
        });

        modalCreateView.on('modal:submit', function(data){
          newTicketModel.save(data, {
            success : function(resultModel){
              isSuccess = true;
              App.trigger('Ticket:view', resultModel.get('id'));
              modalCreateView.$el.modal('hide');
            }
          });
        });

        App.DialogRegion.show(modalCreateView);
        modalCreateView.ModalBody.show(newTicketView);

      });

    };

  });

});