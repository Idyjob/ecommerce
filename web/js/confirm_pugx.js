$(document).ready(function() {
    'use strict';
    /* delete confirm */
    $('form#delete').submit(function (e) {
        var $form = $(this), $hidden = $form.find('input[name="modal"]');
        if ($hidden.val() === '0') {
            e.preventDefault();
            $('#delete_confirm').modal('show');
            $('#delete_confirm').find('button.btn-danger').click(function () {
                $('#delete_confirm').modal('hide');
                $hidden.val('1');
                $form.submit();
            });
        }
    });
});
