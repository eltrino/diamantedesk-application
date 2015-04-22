define(['app'], function(App){

  return App.module('Common.Form', function(Form, App, Backbone, Marionette, $, _){

    Form.LayoutView = Marionette.LayoutView.extend({

      modelEvents: {
        'invalid' : 'formDataInvalid',
        'error' : 'requestReceived',
        'sync' : 'requestReceived'
      },

      ui : {
        'submitButton' : '.js-submit'
      },

      events: {
        'click @ui.submitButton' : 'submitForm'
      },

      submitForm: function(e){
        this.showLoader();
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
        App.debug('warn', 'Validation Errors:', errors);
        this.hideLoader();
        var clearErrors = function(){
          var form = this.$("form");
          form.find(".help-block").remove();
          form.find(".has-error").removeClass("has-error");
        };
        var markErrors = function(value, key){
          var input =  this.$('[name="'+key + '"]'),
              controlGroup = input.parent(),
              errorEl = $("<span>", {class: "help-block", text: value});
          input.after(errorEl);
          controlGroup.addClass("has-error");
          input.change(function(){ errorEl.remove(); controlGroup.removeClass("has-error"); });
        };
        clearErrors.call(this);
        _.each(errors, markErrors, this);

      },

      requestReceived: function(){
        this.hideLoader();
      }

    });

    Form.ItemView = Form.LayoutView;

  });

});