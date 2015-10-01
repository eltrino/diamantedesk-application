/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
define(['jquery', 'underscore', 'oroui/js/modal', 'oroui/js/mediator'],
  function ($, _, Modal, mediator) {
    return function($file){
      var form = document.getElementById('diam-dropzone-form'),
          $attachment = $('#diam-attachments'),
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
              processData: false,
              contentType: false,
              type: 'post',
              success: function(response) {
                  if(response.result){
                      var newElements = template({attachments : response.attachments});
                      $attachment.find('.diam-attachment-new').removeClass('diam-attachment-new');
                      $dropzone.before(newElements);
                  }
                  resetDropZone();
              }
            }).fail(function(){
              var dialog = new Modal({
                content: 'Something went wrong, try upload file once more',
                cancelText: 'Close',
                title: 'File Upload Error',
                className: 'modal oro-modal-danger'
              });
              resetDropZone();
              dialog.open()
            });
          },
          resetDropZone = function(){
            $label.show();
            $loader.hide();
            $dropzone.removeClass('diam-dropzone-active');
            form.reset();
          },
          onDragStart = function(event) {
            if ($.inArray('Files', event.originalEvent.dataTransfer.types) > -1) {
              event.stopPropagation();

              $dropzone.addClass('diam-dropzone-active');
              dropZoneVisible= true;

              event.originalEvent.dataTransfer.effectAllowed= 'none';
              event.originalEvent.dataTransfer.dropEffect= 'none';
              if(event.target.id == 'diam-dropzone-file') {
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

      if($.uniform) {
        $.uniform.restore($file);
      }

      mediator.on('page:afterChange', function () {
        $.uniform.restore($file);
      });

      $file.on('change', onChange);
    }
  }
);
