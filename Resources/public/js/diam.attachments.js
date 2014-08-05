define(['jquery', 'underscore'],
  function ($, _) {
    var $attachments = $('#diam-attachments'),
        form = document.getElementById('diam-dropzone-form'),
        $dropzone = $('#diam-dropzone'),
        $label = $('#diam-dropzone-label'),
        $loader = $('#diam-dropzone-loader'),
        file = document.createElement('input'),
        template = _.template(document.getElementById('template-attachments').innerHTML);

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
