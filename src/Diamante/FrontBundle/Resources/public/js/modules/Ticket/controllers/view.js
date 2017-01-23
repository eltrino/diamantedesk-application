define(['app'], function(App){

  return App.module('Ticket.View', function(View, App, Backbone, Marionette, $, _){

    View.Controller = function(id, backUrl){

      App.mainRegion.showLoader();

      require(['Ticket/models/ticket', 'Ticket/views/view'], function(){

        App.request('ticket:model', id).done(function(ticketModel){

          var ticketView = new View.ItemView({
                model : ticketModel,
                backUrl : backUrl
              });

          App.setTitle(_.template('[#<%=key%>] <%=subject%>')(ticketModel.toJSON()));

          ticketView.on('dom:refresh', function(){
            require(['Comment', 'Attachment', 'Watcher'], function(Comment, Attachment, Watcher){
              var commentOptions = {
                    ticket : this.model,
                    parentRegion : this.commentsRegion
                  },
                  attachmentOptions = {
                    ticket : this.model,
                    parentRegion : this.attachmentsRegion
                  },
                  watcherOptions = {
                    ticket : this.model,
                    parentRegion : this.watchersRegion
                  };
              if(Comment.ready){
                Comment.render(commentOptions);
              } else {
                Comment.start(commentOptions);
              }
              if(Attachment.ready){
                Attachment.render(attachmentOptions);
              } else {
                Attachment.start(attachmentOptions);
              }
              if(Watcher.ready){
                Watcher.render(watcherOptions);
              } else {
                Watcher.start(watcherOptions);
              }

            }.bind(this));
          });

          ticketView.on('destroy', function(){
            require(['Comment', 'Attachment', 'Watcher'], function(Comment, Attachment, Watcher){
              Comment.stop();
              Attachment.stop();
              Watcher.stop();
            });
          });

          ticketView.on('ticket:close', function(){
            ticketView.showLoader();
            ticketModel.save({'status':'closed'}, {patch : true, wait: true}).done(
              function(){
                App.trigger('message:show', {
                  status:'success',
                  text: __('diamante_front.ticket.controller.message.status_closed', {ticket_status_closed_id: ticketModel.get('key')})
                });
              }
            );
          });

          ticketView.on('ticket:open', function(){
            ticketView.showLoader();
            ticketModel.save({'status':'open'}, {patch : true, wait: true}).done(
              function(){
                App.trigger('message:show', {
                  status:'success',
                  text: __('diamante_front.ticket.controller.message.status_open', {ticket_status_open_id: ticketModel.get('key')})
                });
              }
            );
          });

          App.mainRegion.show(ticketView);

        }).fail(function(model, xhr){

          var link, key;

          if(xhr.status === 500){
            App.trigger('message:show', {
              status: 'error',
              text: xhr.responseJSON.message
            });
            App.mainRegion.hideLoader();
          } else if (xhr.status === 301) {
            link = document.createElement('a');
            link.href = xhr.getResponseHeader('X-Location');
            key = decodeURIComponent(link.href.replace(model.urlRoot,'').replace('/',''));
            App.trigger('message:show', {
              status:'info',
              text: __('diamante_front.ticket.controller.message.key_changed', {ticket_key_id: key})
            });
            App.trigger('ticket:view', key, backUrl);
          } else {
            App.mainRegion.show(new View.MissingView());
          }

        });

      });

    };

  });

});