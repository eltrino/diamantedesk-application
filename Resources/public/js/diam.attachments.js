define(['jquery', 'underscore', 'oroui/js/modal'],
  function ($, _, Modal) {
    return function($file){
      var form = document.getElementById('diam-dropzone-form'),
          $dropzone = $('#diam-dropzone'),
          $label = $('#diam-dropzone-label'),
          $loader = $('#diam-dropzone-loader'),
          template = _.template(document.getElementById('template-attachments').innerHTML),
          dropZoneHideDelay = 70,
          dropZoneTimer = null,
          dropZoneVisible = null,

          onChange = function () {
            var data = new FormData(form);
            data.append('diam-dropzone', 1);

            $label.hide();
            $loader.show();

            $.ajax({
              url: form.action,
              data: data,
              dataType: 'json',
              processData: false,
              contentType: false,
              type: 'POST'
            }).done(function(json){
              var newElements = template({attachments : json});
              $dropzone.find('.diam-attachment-new').removeClass('diam-attachment-new');
              $dropzone.before(newElements);
            }).always(function(){
              $label.show();
              $loader.hide();
              $dropzone.removeClass('diam-dropzone-active');
              form.reset();
            }).fail(function(){
              var dialog = new Modal({
                content: 'Something went wrong, try upload file once more',
                cancelText: 'Close',
                title: 'File Upload Error',
                className: 'modal oro-modal-danger'
              });
              dialog.open()
            });

          },
          onDragStart = function(event) {
            if ($.inArray('Files', event.originalEvent.dataTransfer.types) > -1) {
              event.stopPropagation();

              $dropzone.addClass('diam-dropzone-active');
              dropZoneVisible= true;

              event.originalEvent.dataTransfer.effectAllowed= 'none';
              event.originalEvent.dataTransfer.dropEffect= 'none';
              if(event.target.id = 'diam-dropzone-file') {
                event.originalEvent.dataTransfer.effectAllowed= 'copyMove';
                event.originalEvent.dataTransfer.dropEffect= 'move';
              } else {
                event.preventDefault();
              }
            }
          },
          onDragEnd = function (event) {
            dropZoneVisible= false;

            clearTimeout(dropZoneTimer);
            dropZoneTimer = setTimeout( function(){
              if( !dropZoneVisible ) {
                $dropzone.removeClass('diam-dropzone-active');
              }
            }, dropZoneHideDelay);
          };

      $(document).off('dragstart dragenter dragover', onDragStart).off('drop dragleave dragend', onDragEnd);
      $(document).on('dragstart dragenter dragover', onDragStart).on('drop dragleave dragend', onDragEnd);

      $file.on('uniformInit', function(){
        $.uniform.restore($file);
      });
      if($.uniform) {
        $.uniform.restore($file);
      }


      $file.on('change', onChange);
    }
  }
);
