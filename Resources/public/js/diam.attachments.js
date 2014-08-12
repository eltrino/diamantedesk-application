define(['jquery', 'underscore'],
  function ($, _) {
    var $attachments = $('#diam-attachments'),
        form = document.getElementById('diam-dropzone-form'),
        $dropzone = $('#diam-dropzone'),
        $label = $('#diam-dropzone-label'),
        $loader = $('#diam-dropzone-loader'),
        file = document.createElement('input'),
        template = _.template(document.getElementById('template-attachments').innerHTML),
        dropZoneHideDelay = 70,
        dropZoneTimer = null,
        dropZoneVisible = null;

    $(document).on('dragstart dragenter dragover', function(event) {
      if ($.inArray('Files', event.originalEvent.dataTransfer.types) > -1) {
        event.stopPropagation();


        $dropzone.addClass('diam-dropzone-active');
        dropZoneVisible= true;

        event.originalEvent.dataTransfer.effectAllowed= 'none';
        event.originalEvent.dataTransfer.dropEffect= 'none';

        if(event.target == file) {
          event.originalEvent.dataTransfer.effectAllowed= 'copyMove';
          event.originalEvent.dataTransfer.dropEffect= 'move';
        } else {
          event.preventDefault();
        }
      }
    }).on('drop dragleave dragend', function (event) {
      dropZoneVisible= false;

      clearTimeout(dropZoneTimer);
      dropZoneTimer = setTimeout( function(){
        if( !dropZoneVisible ) {
          $dropzone.removeClass('diam-dropzone-active');
        }
      }, dropZoneHideDelay);
    });

    file.type = 'file';
    file.name = 'diamante_attachment_form[files][]';
    file.multiple = true;

    $(window).load(function () {
      $(form).append(file);
    });

    file.addEventListener('change', function () {

      $label.hide();
      $loader.show();

      $.ajax({
        url: form.action,
        data:  new FormData(form),
        processData: false,
        contentType: false,
        type: 'POST'
      }).done(function(response){
        var attachments =  $.parseJSON(response),
            newElements = template({attachments : attachments});
        $label.show();
        $loader.hide();
        $dropzone.removeClass('diam-dropzone-active');
        $dropzone.before(newElements);
        form.reset();
      });

    }, false);
  }
);
