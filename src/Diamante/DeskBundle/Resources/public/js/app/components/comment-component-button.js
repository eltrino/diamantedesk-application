define(function (require) {

  "use strict";

  var $ = require('jquery');
  var BaseComponent = require('oroui/js/app/components/base/component');

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
      this.form.submit();
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
