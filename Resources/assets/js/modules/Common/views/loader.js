define(['app', 'tpl!../templates/loader.ejs'], function(App, loaderTemplate){

  App.module('Common.Loader', function(Loader, App, Backbone, Marionette, $, _){

    Loader.View = Marionette.ItemView.extend({
      template : loaderTemplate,
      className: 'loader'
    });

    var loader = new Loader.View().render().el;

    _.extend(Marionette.Region.prototype,{
      showLoader: function(){
        if(this.hasView()){
          this.$el.prepend(loader);
        } else {
          this.$el = this.getEl(this.el);
          this.el = this.$el[0];
          this.$el.html(loader);
        }
      },
      hideLoader: function(){
        loader.remove();
      }
    });
    _.extend(Marionette.View.prototype,{
      showLoader: function(){
        this.$el.parent().prepend(loader);
        this.once('dom:refresh', function(){
          this.hideLoader();
        });
      },
      hideLoader: function(){
        this.$el.removeClass('loading');
        loader.remove();
      }
    });

  });

  return new App.Common.Loader.View();

});