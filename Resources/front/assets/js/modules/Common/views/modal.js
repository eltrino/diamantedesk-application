define(['tpl!modules/Common/templates/modal.ejs'], function(modalTemplate){

  App.module('Common.Modal', function(Modal, App, Backbone, Marionette, $, _){

    Modal.CompositeView = Marionette.CompositeView.extend({
      className: 'modal fade',
      template: modalTemplate,
      childViewContainer: "div.modal-body",

      events: {
        'show.bs.modal': "beforeShowModal",
        'hidden.bs.modal': "hideModal"
      },

      beforeShowModal: function(){
        $('body').addClass('blured');
      },

      hideModal: function(){
        $('body').removeClass('blured');
        this.trigger('modal:closed');
      }
    });

  });

});