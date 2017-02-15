define(['app', 'tpl!../templates/modal.ejs'], function(App, modalTemplate){

  return App.module('Common.Modal', function(Modal, App, Backbone, Marionette, $, _){

    var body = $('body'),
        win = $(window),
        silent = false;

    Modal.LayoutView = Marionette.LayoutView.extend({
      className: 'modal fade',
      template: modalTemplate,

      initialize: function(options){
        var opt = options || {};
        this.title = opt.title || __('diamante_front.common.view.modal.title');
        this.submit = opt.submit ||  __('diamante_front.common.view.modal.submit');
      },

      regions : {
        modalBody : 'div.modal-body'
      },

      templateHelpers: function(){
        return {
          submit: this.submit,
          title: this.title
        };
      },

      events: {
        'show.bs.modal': 'beforeShowModal',
        'hide.bs.modal': 'beforeHideModal',
        'hidden.bs.modal': 'hideModal',
        'click .js-save-btn': 'submitModal'
      },

      onRoute: function(e){
        silent = true;
        this.$el.modal('hide');
      },

      beforeShowModal: function(){
        body.addClass('blured');
        silent = false;
        win.on("hashchange.modal", this.onRoute.bind(this));
      },

      beforeHideModal: function() {
        win.off("hashchange.modal");
      },

      hideModal: function(){
        body.removeClass('blured');
        if(!silent){
          this.trigger('modal:closed');
        }
        this.destroy();
      },

      submitModal: function(){
        this.trigger('modal:submit');
      }

    });

  });

});