define(['jquery', 'underscore'],
  function ($, _) {
    var $attachments = $('#diam-attachments'),
        form = document.getElementById('diam-dropzone-form'),
        $dropzone = $('#diam-dropzone'),
        $label = $('#diam-dropzone-label'),
        $loader = $('#diam-dropzone-loader'),
        file = document.createElement('input');

    $attachments[0].addEventListener('dragenter', function (e) {
      console.log('enter');
      $dropzone.addClass('diam-dropzone-active');
    }, false);

    $attachments[0].addEventListener('dragleave', function (e) {
      console.log('leave');
      $dropzone.removeClass('diam-dropzone-active');
    }, false);

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
        var div = document.createElement('div');
        $label.show();
        $loader.hide();
        $dropzone.removeClass('diam-dropzone-active');
        div.innerHTML = req.response;
        $attachments.append(div.firstChild);
        form.reset();
      };

      req.open(form.method, form.action, true);
      req.send(formData);

    }, false);
  }
);
