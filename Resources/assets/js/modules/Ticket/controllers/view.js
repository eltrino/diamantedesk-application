define(['app'], function(App){

  return App.module('Ticket.View', function(View, App, Backbone, Marionette, $, _){

    View.Controller = function(id){

      require(['Ticket/models/ticket', 'Ticket/views/view'], function(){

        App.request('ticket:model', id).done(function(ticketModel){

          var ticketView = new View.ItemView({
              model : ticketModel
          });
          App.setTitle(_.template('[#<%=key%>] <%=subject%>')(ticketModel.toJSON()));
          ticketView.on('dom:refresh', function(){
            require(['Comment','Attachment'], function(Comment, Attachment){
              var commentOptions = {
                    ticket : this.model,
                    parentRegion : this.commentsRegion
                  },
                  attachmentOptions = {
                    ticket : this.model,
                    parentRegion : this.attachmentsRegion
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

            }.bind(this));
          });
          ticketView.on('destroy', function(){
            require(['Comment', 'Attachment'], function(Comment, Attachment){
              Comment.stop();
              Attachment.stop();
            });
          });
          ticketView.on('ticket:close', function(){
            ticketModel.save({'status':'closed'}, {patch : true});
          });
          ticketView.on('ticket:open', function(){
            ticketModel.save({'status':'open'}, {patch : true});
          });

          App.mainRegion.show(ticketView);

        }).fail(function(){

          var missingView = new View.MissingView();
          App.mainRegion.show(missingView);

        });

      });

    };

  });

});