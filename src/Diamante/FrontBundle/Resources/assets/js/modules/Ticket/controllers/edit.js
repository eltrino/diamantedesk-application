define(['app'], function(App){

  return App.module('Ticket.Edit', function(Edit, App, Backbone, Marionette, $, _){

    Edit.Controller = function(key){

      App.mainRegion.showLoader();

      require([
        'Ticket/models/ticket',
        'Ticket/views/edit'], function(Models, EditView){

        App.request('ticket:model', key).done(function(editTicketModel){

          var editTicketView = new Edit.ItemView({
                model: editTicketModel
              }),
              modalEditView = new Edit.ModalView({
                title: 'Edit Ticket ' + editTicketModel.get('key')
              });

          App.setTitle(_.template('Edit Ticket: <%=key%> - <%=subject%>')(editTicketModel.toJSON()));

          modalEditView.on('show', function(){
            this.$el.modal();
          });

          modalEditView.on('modal:closed', function(){
            App.trigger('ticket:view', editTicketModel.get('key'));
          });

          editTicketView.on('form:submit', function(data){
            this.model.set(data);
            var attrs = this.model.changedAttributes();
            if(attrs){
              this.model.save(attrs,{
                patch : true,
                success : function(resultModel){
                  App.trigger('ticket:view', resultModel.get('key'));
                  App.trigger('message:show', {
                    status:'success',
                    text: 'Ticket ' + resultModel.get('key') + ' updated'
                  });
                  modalEditView.off('modal:closed');
                  modalEditView.$el.modal('hide');
                },
                error : function(){
                  App.alert({title: "Edit Ticket Error"});
                }
              });
            } else {
              modalEditView.$el.modal('hide');
            }
          });

          App.debug('log', App.mainRegion.hasView());
          App.dialogRegion.show(modalEditView);
          modalEditView.modalBody.show(editTicketView);

        }).fail(function(model, xhr){

          var link, key;

          if (xhr.status === 301) {
            link = document.createElement('a');
            link.href = xhr.getResponseHeader('X-Location');
            key = decodeURIComponent(link.href.replace(model.urlRoot,'').replace('/',''));
            App.trigger('message:show', {
              status:'info',
              text: 'While we were actively working on your ticket, its key was changed to ' + key
            });
            App.trigger('ticket:edit', key);
          } else {
            App.mainRegion.show(new Edit.MissingView());
          }

        });

      });

    };

  });

});