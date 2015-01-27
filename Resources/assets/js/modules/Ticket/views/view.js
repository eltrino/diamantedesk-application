define([
  'app',
  'tpl!../templates/view.ejs',
  'tpl!../templates/missing-view.ejs'], function(App, TicketViewTemplate, missingTicketViewTemplate){

  return App.module('Ticket.View', function(View, App, Backbone, Marionette, $, _){

    View.MissingView = Marionette.ItemView.extend({
      template: missingTicketViewTemplate
    });

    View.ItemView = Marionette.LayoutView.extend({
      className: 'ticket-view',
      template: TicketViewTemplate,

      initialize: function() {
        this.listenTo(this.model, "change:status", this.render);
      },

      regions: {
        CommentsRegion: '#comments'
      },

      templateHelpers: function(){
        return {
          created : new Date(this.model.get('created_at')).toLocaleDateString()
        };
      },

      events : {
        'click .js-back' : 'back',
        'click .js-edit-ticket' : 'editTicket',
        'click .js-close-ticket' : 'resolveTicket',
        'click .js-open-ticket' : 'reopenTicket'
      },

      back : function(e){
        e.preventDefault();
        App.back();
      },

      editTicket : function(e){
        e.preventDefault();
        App.trigger('ticket:edit', this.model.get('id'));
      },

      resolveTicket : function(e){
        e.preventDefault();
        this.trigger('ticket:close');
      },

      reopenTicket : function(e){
        e.preventDefault();
        this.trigger('ticket:open');
      }

    });

  });

});