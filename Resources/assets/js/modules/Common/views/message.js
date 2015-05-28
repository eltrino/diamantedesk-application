define(['app', 'tpl!../templates/message.ejs'], function(App, massageTemplate){

  return App.module('Common.Message', function(Message, App, Backbone, Marionette, $, _){

    var _status = {
          'error' : {
            status_class : 'danger',
            status_icon : 'exclamation-circle'
          },
          'success' : {
            status_class : 'success',
            status_icon : 'check-circle'
          },
          'info' : {
            status_class : 'info',
            status_icon : 'info-circle'
          },
          'warning' : {
            status_class : 'warning',
            status_icon : 'exclamation-circle'
          }
        },
        w = window,
        doc = document.documentElement,
        body = $('body')[0];

    Message.View = Marionette.ItemView.extend({
      template : massageTemplate,
      className: 'messages',

      constructor : function(options){
        var messages = new Backbone.Collection();
        if(_.isString(options)){
          messages.add({
            status : 'info',
            text : options
          });
        } else {
          messages.add(options);
        }
        Marionette.ItemView.call(this, { collection: messages } );
      },

      templateHelpers: function(){
        return {
          status_class : function(status){
            return _status[status].status_class;
          },
          status_icon : function(status){
            return _status[status].status_icon;
          }
        };
      },

      onShow: function(){
        var fixedElem = this.$el.clone(),
            headerHeight = App.headerRegion.$el.height(),
            elemBottom = this.$el.offset().top + this.$el.height(),
            inView = true,
            checkPosition = function() {
              var scrollTop = w.pageYOffset || doc.scrollTop || body.scrollTop,
                  topOffset = scrollTop + headerHeight;
              if(topOffset > elemBottom){
                inView = false;
                if(!fixedElem.is(':visible')){
                  fixedElem.fadeIn(400);
                }
              } else {
                inView = true;
                if(fixedElem.is(':visible')){
                  fixedElem.fadeOut(400);
                }
              }
            }.bind(this);

        fixedElem.addClass('fixed-messages').hide().insertAfter(this.$el);

        $(w).on('resize scroll', checkPosition);
        checkPosition();

        this.$el.hide().slideDown(inView ? 400 : 0)
          .delay(5000).slideUp(inView ? 400 : 0, function(){
            this.destroy();
            fixedElem.fadeOut(400, function(){ this.remove(); });
          }.bind(this));
      }

    });

  });

});