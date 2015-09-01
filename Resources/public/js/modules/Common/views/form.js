define(['app', 'config', 'tinymce'], function(App, Config){

  var tinymce_options = {
    menubar : false,
    resize: true,
    autoresize_bottom_margin: 0,
    plugins: ['textcolor', 'code', 'link', 'autoresize'],
    toolbar: ['undo redo | bold italic underline | forecolor backcolor | bullist numlist | link | code'],
    skin_url: Config.basePath + '/bundles/diamantefront/js/vendor/tinymce/skins/lightgray',
    content_css: Config.basePath + '/bundles/diamantefront/css/wysiwyg.css'
  };

  return App.module('Common.Form', function(Form, App, Backbone, Marionette, $, _){

    Form.LayoutView = Marionette.LayoutView.extend({

      modelEvents: {
        'invalid' : 'formDataInvalid',
        'error' : 'requestReceived',
        'sync' : 'requestReceived'
      },

      ui : {
        'submitButton' : '.js-submit',
        'form': 'form'
      },

      events: {
        'click @ui.submitButton' : 'submitForm',
        'submit @ui.form' : 'submitForm'
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
      },

      onShow: function() {
        var textarea = this.$('textarea');
        if(textarea.tinymce()){
          textarea.tinymce().remove();
        }
        textarea.tinymce(tinymce_options);
      }

    });

    Form.ItemView = Form.LayoutView;

  });

});