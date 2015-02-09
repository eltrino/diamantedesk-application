define([
  'app',
  'tpl!../templates/dropzone.ejs'], function(App, dropzoneTemplate){

  return App.module('Ticket.View.Attachment.DropZone', function(DropZone, App, Backbone, Marionette, $, _){

    var $doc = $(document),
        dropZoneHideDelay = 70,
        dropZoneTimer = null,
        dropZoneVisible = null;

    DropZone.ItemView = Marionette.ItemView.extend({
      template: dropzoneTemplate,
      className: 'dropzone',

      ui: {
        fileInput: '.dropzone-input'
      },

      events: {
        'change @ui.fileInput' : 'addFile'
      },

      initialize: function(){
        this.onDragStart = this.dragStart.bind(this);
        this.onDragEnd = this.dragEnd.bind(this);
      },

      addFile: function(){
        var form = $('<form />'), data;
        form.append(this.ui.fileInput);
        data = new FormData(form[0]);
        this.trigger('attachment:add', data);

        //var files = this.ui.fileInput[0].files,
        //    data = [],
        //    ready = 0;
        //_.each(files, function(file){
        //  var reader = new FileReader(),
        //      i = data.length;
        //  data[i] = {
        //    filename : file.name
        //  };
        //  reader.onloadend = function () {
        //    data[i].content = reader.result;
        //    if(files.length == ++ready){
        //      this.trigger('attachment:add', data);
        //    }
        //  }.bind(this);
        //  reader.readAsDataURL(file);
        //}, this);
      },

      success: function(){
        this.$el.append(this.ui.fileInput);
      },

      dragStart: function(e){
        if ($.inArray('Files', e.originalEvent.dataTransfer.types) > -1) {
          e.stopPropagation();

          this.$el.addClass('dropzone-active');
          dropZoneVisible= true;

          e.originalEvent.dataTransfer.effectAllowed= 'none';
          e.originalEvent.dataTransfer.dropEffect= 'none';
          if(e.target == this.ui.fileInput[0]) {
            e.originalEvent.dataTransfer.effectAllowed= 'copyMove';
            e.originalEvent.dataTransfer.dropEffect= 'move';
          } else {
            event.preventDefault();
          }
        }
      },

      dragEnd: function(){
        dropZoneVisible= false;

        clearTimeout(dropZoneTimer);
        dropZoneTimer = setTimeout( function(){
          if( !dropZoneVisible ) {
            this.$el.removeClass('dropzone-active');
          }
        }.bind(this), dropZoneHideDelay);

      },

      onShow: function(){
        $doc.on('dragstart dragenter dragover', this.onDragStart).on('drop dragleave dragend', this.onDragEnd);
      },

      onDestroy: function(){
        $doc.off('dragstart dragenter dragover', this.onDragStart).off('drop dragleave dragend', this.onDragEnd);
      }
    });

  });

});