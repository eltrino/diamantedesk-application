define(['app'], function(App){

  return App.module('Ticket.View.Attachment', function(Attachment, App, Backbone, Marionette, $, _){

    Attachment.Controller = function(options){

      require([
        'Attachment/models/attachment',
        'Attachment/controllers/list',
        'Attachment/controllers/create',
        'Attachment/views/layout'], function(Models, List, Create){

        var ticket = options.ticket,
            attachmentCollection = new Models.Collection(ticket.get('attachments'), { ticket : ticket }),
            attachmentLayoutView = new Attachment.LayoutView();

        options.parentRegion.show(attachmentLayoutView);

        List.Controller({
          ticket: ticket,
          parentView : attachmentLayoutView,
          collection : attachmentCollection
        });

        Create.Controller({
          ticket: ticket,
          parentView : attachmentLayoutView,
          collection : attachmentCollection
        });

      });

    };

  });

});