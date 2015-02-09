define(['app'], function(App){

  return App.module('Ticket.View.Attachment.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.Controller = function(options){
      require([
        'Attachment/models/attachment',
        'Attachment/views/dropzone'], function(Models, DropZone){

        var attachmentModel = new Models.Model({},{ ticket : options.ticket }),
            attachmentCollection = options.collection,
            dropView = new DropZone.ItemView({ model: attachmentModel });

        dropView.on('attachment:add', function(data){

          data.append('ticketId', options.ticket.id);
          attachmentModel.save({},{
            data: data,
            processData: false,
            contentType: false
          });

          //attachmentModel.save({
          //  'ticketId': options.ticket.id,
          //  'attachmentsInput' : data[0]
          //});
        });

        options.parentView.dropRegion.show(dropView);

      });
    };

  });

});