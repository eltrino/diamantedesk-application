define([
  'app',
  'moment',
  'tpl!../templates/view.ejs',
  'tpl!../templates/empty-view.ejs'], function(App, moment, TicketViewTemplate, missingTicketViewTemplate){

  return App.module('Ticket.View', function(View, App, Backbone, Marionette, $, _){

    View.MissingView = Marionette.ItemView.extend({
      template: missingTicketViewTemplate
    });

    View.ItemView = Marionette.LayoutView.extend({
      className: 'ticket-view',
      template: TicketViewTemplate,

      initialize: function(options) {
        this.listenTo(this.model, "change:status", this.render);
        this.backUrl = options.backUrl;
      },

      regions : {
        commentsRegion : '#comments',
        attachmentsRegion : '#attachments',
        watchersRegion : '#watchers'
      },

      templateHelpers : function(){
        var date = moment(this.model.get('created_at'));
        return {
          created: new Date(date.toDate()).toLocaleDateString(),
          status: this.model.get('status').replace(/_/g,' '),
          back_url: this.backUrl ? '#tickets' + this.backUrl : '#tickets'
        };
      },

      ui : {
        backButton : '.js-back',
        editButton : '.js-edit-ticket',
        closeButton : '.js-close-ticket',
        openButton : '.js-open-ticket'
      },

      events : {
        'click @ui.backButton' : 'back',
        'click @ui.editButton' : 'editTicket',
        'click @ui.closeButton' : 'resolveTicket',
        'click @ui.openButton' : 'reopenTicket'
      },

      back : function(e){

      },

      editTicket : function(e){
        e.preventDefault();
        App.trigger('ticket:edit', this.model.get('key'));
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