define(['app'], function(App){

  return App.module('Ticket.View.Attachment.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.Controller = function(options){
      require([
        'Attachment/models/attachment',
        'Attachment/views/dropzone'], function(Models, DropZone){

        var attachmentCollection = options.collection,
            dropView = new DropZone.ItemView();

        dropView.on('attachment:add', function(data){
          dropView.trigger('progress', 'sending', 0);
          var newAttachments = new Models.Collection(data,{ ticket: options.ticket });
          newAttachments.on('progress', function(state, value){
            dropView.trigger('progress', state, value);
          });
          newAttachments.save({
            success: function(collection){
              attachmentCollection.add(collection, { ticket: options.ticket });
              Create.Controller(options);
            }
          });
        });

        options.parentView.dropRegion.show(dropView);

      });
    };

  });

});