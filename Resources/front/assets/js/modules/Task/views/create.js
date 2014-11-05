define(['tpl!modules/Task/templates/create.ejs'], function(createTemplate){

  App.module('Task.Create', function(Create, App, Backbone, Marionette, $, _){

    Create.ItemView = Marionette.ItemView.extend({
      className: 'modal fade',
      template: createTemplate,

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