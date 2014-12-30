define(['app'], function(App){

  return App.module('Ticket.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.Controller = function(){

      require(['Ticket/models/ticket', 'Ticket/views/create'], function(Models, CreateView){

        var isSuccess = false,
            newTicketModel = new Models.Model(),
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

        newTicketView.on('form:submit', function(data){
          data.branch = 1;
          App.request("user:model:current").done(function(user){
            data.reporter =  'oro_' + user.get('id');
            data.assignee =  user.get('id');
            newTicketModel.save(data, {
              success : function(resultModel){
                isSuccess = true;
                App.trigger('ticket:view', resultModel.get('id'));
                modalCreateView.$el.modal('hide');
              }
            });
          });

        });

        App.DialogRegion.show(modalCreateView);
        modalCreateView.ModalBody.show(newTicketView);

      });

    };

  });

});