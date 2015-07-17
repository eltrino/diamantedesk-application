define(['app'], function(App){

  return App.module('Ticket.View.Watcher.Add', function(Add, App, Backbone, Marionette, $, _){

    Add.Controller = function(options){
      require([
        'Watcher/models/watcher',
        'Watcher/views/add'], function(WatcherModel, AddView){

        var watcherModel = new WatcherModel.Model({},{ ticket : options.ticket }),
            watcherCollection = options.collection,
            addView = new AddView.ItemView({
              model: watcherModel
            }),
            modalAddView = new AddView.ModalView({
              title: 'Add Watcher',
              submit: 'Add'
            });

        modalAddView.on('show', function(){
          this.$el.modal();
        });

        addView.on('form:submit', function(attr){
          watcherModel.save(attr,{
            success: function(model){
              watcherCollection.add(model);
              modalAddView.$el.modal('hide');
            },
            error: function(model, xhr){
              App.alert({
                title: "Add Watcher Error",
                xhr : xhr
              });
            }
          });
        });

        App.dialogRegion.show(modalAddView);
        modalAddView.modalBody.show(addView);

      });
    };

  });

});