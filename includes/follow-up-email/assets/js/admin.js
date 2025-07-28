jQuery(function ($) {
    // Media upload for attachment
    $(document).on('click', '#attachment_id_button', function (e) {
        e.preventDefault();
        var button = $(this);
        var fileFrame = wp.media({
            title: houses_follow_email.choose_file_text || 'Select file',
            button: { text: houses_follow_email.use_file_text || 'Use this file' },
            multiple: false
        });

        fileFrame.on('select', function () {
            var attachment = fileFrame.state().get('selection').first().toJSON();
            $('#attachment_id').val(attachment.id);
            button.siblings('.file-preview').text(attachment.url);
        });

        fileFrame.open();
    });

    // Send email
    $('#houses-send-follow-email').on('click', function () {
        var btn = $(this);
        btn.prop('disabled', true).text('Sending...');
        $.post(houses_follow_email.ajax_url, {
            action: 'houses_send_follow_up_email',
            nonce: houses_follow_email.nonce,
            post_id: houses_follow_email.post_id
        }).done(function (resp) {
            if (resp.success) {
                alert(resp.data);
                // Reload to update meta values
                location.reload();
            } else {
                alert(resp.data || 'Error sending email');
            }
        }).fail(function () {
            alert('Error sending email');
        }).always(function () {
            btn.prop('disabled', false).text('Send Email');
        });
    });
});
