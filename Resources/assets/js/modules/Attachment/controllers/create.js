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
          //data.append('ticketId', options.ticket.id);
          //attachmentModel.save({},{
          //  data: data,
          //  processData: false,
          //  contentType: false
          //});

          var attr = {
            ticketId: options.ticket.id,
            attachmentsInput : data
          };
          attachmentModel.save(attr, {
            success: function(model){
              attachmentCollection.add(model);
              Create.Controller(options);
            },
            error: function(model, xhr){
              App.alert({
                title: "Add Attach Error",
                xhr : xhr
              });
            }
          });
        });

        options.parentView.dropRegion.show(dropView);

      });
    };

  });

});