define(function (require) {

  "use strict";

  var $ = require('jquery');
  var BaseComponent = require('oroui/js/app/components/base/component');
  var Mediator = require('oroui/js/mediator');
  var formToAjaxOptions = require('oroui/js/tools/form-to-ajax-options');

  var CommentComponentButton = BaseComponent.extend({

    initialize : function(options){
      this.$elem  = options._sourceElement;
      this.$elem.find('.btn-group').addClass('dropup');
      this.form = $('form[name="'+ options.formName +'"]');
      this.$elem.on('click', 'a', this.clickHandler.bind(this));
    },

    clickHandler : function(e){
      e.preventDefault();
      var target = $(e.target),
          data = target.data();
      if(!data.commentAction) {
        return;
      }
      this[data.commentAction](data);
    },

    add: function(data){
      var options = formToAjaxOptions(this.form);
      //this.form.submit();
      Mediator.execute('showLoading');
      Mediator.execute('submitPage', options);
    },

    set: function(data){
      this.form.find('[name="'+ data.fieldName +'"]').val(data.fieldValue);
      this.add();
    },

    dispose: function () {
      if (this.disposed) {
        return;
      }
      delete this.$elem;
    }

  });

  return CommentComponentButton;


});
