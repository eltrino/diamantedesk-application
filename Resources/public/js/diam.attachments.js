define(['jquery', 'underscore'],
  function ($, _) {
    var $attachments = $('#diam-attachments'),
        form = document.getElementById('diam-dropzone-form'),
        $dropzone = $('#diam-dropzone'),
        $label = $('#diam-dropzone-label'),
        $loader = $('#diam-dropzone-loader'),
        file = document.createElement('input');

    $attachments.on('dragenter', function (e) {
      console.log('enter');
      $dropzone.addClass('diam-dropzone-active');
    });

    $dropzone.on('dragleave', function (e) {
      console.log('leave');
      $dropzone.removeClass('diam-dropzone-active');
    });

    file.type = 'file';
    file.name = 'diamante_attachment_form[files][]';
    file.multiple = true;
    $(window).load(function () {
      form.appendChild(file)
    });

    file.addEventListener('change', function () {
      var req = new XMLHttpRequest(),
          formData = new FormData(form);

      $label.hide();
      $loader.show();

      req.onload = function () {
        var newElements = $.parseHTML(req.response);
        newElements.addClass('diam-attachment-new')
        $label.show();
        $loader.hide();
        $dropzone.removeClass('diam-dropzone-active');
        $dropzone.before(newElements);
        form.reset();
      };

      req.open(form.method, form.action, true);
      req.send(formData);

    }, false);
  }
);
