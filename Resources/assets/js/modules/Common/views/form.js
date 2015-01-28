define(['app'], function(App){

  return App.module('Common.Form', function(Form, App, Backbone, Marionette, $, _){

    Form.ItemView = Marionette.ItemView.extend({

      modelEvents: {
        'invalid' : 'formDataInvalid'
      },

      ui : {
        'submitButton' : '.js-submit'
      },
      events: {
        'click @ui.submitButton' : 'submitForm'
      },

      submitForm: function(e){
        if(e) {
          e.preventDefault();
        }
        var arr = this.$('form').serializeArray(), i = arr.length, data = {}, elem;
        for(;i--;) {
          elem = arr[i];
          if(elem.type === 'text' || elem.type === 'textarea'){
            data[elem.name] = arr[i].value.toString();
          } else {
            data[elem.name] = arr[i].value;
          }
        }
        this.ui.submitButton.blur();
        this.trigger('form:submit', data);
      },

      clearForm : function(){
        this.$('form')[0].reset();
      },

      formDataInvalid: function (model ,errors){
        var clearErrors = function(){
          var $form = this.$("form");
          $form.find(".help-block").remove();
          $form.find(".has-error").removeClass("has-error");
        };
        var markErrors = function(value, key){
          var $controlGroup = this.$('[name="'+key + '"]').parent();
          var $errorEl = $("<span>", {class: "help-block", text: value});
          $controlGroup.append($errorEl).addClass("has-error");
        };
        clearErrors.call(this);
        _.each(errors, markErrors, this);

      }

  });

  });

});