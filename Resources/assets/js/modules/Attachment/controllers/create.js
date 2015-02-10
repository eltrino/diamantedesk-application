define(['app'], function(App){

  return App.module('Ticket.View.Attachment.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.Controller = function(options){
      require([
        'Attachment/models/attachment',
        'Attachment/views/dropzone'], function(Models, DropZone){

        var attachmentModel = new Models.Model({},{ ticket: options.ticket }),
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
            attachmentsInput : data
          };
          $.ajax({
            url:attachmentModel.urlRoot,
            type:'post',
            data: attr,
            success: function(collection){
              attachmentCollection.add(collection, { ticket: options.ticket });
              Create.Controller(options);
            },
            error: function(xhr){
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