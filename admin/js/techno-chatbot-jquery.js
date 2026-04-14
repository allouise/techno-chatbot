(function( $ ) {
'use strict';
    /* Color Picker */
    $('.techno-color-field').wpColorPicker({ palettes: true });

    /* Media Uploader */
    $('.techno-upload-button').click(function (e) {
        e.preventDefault();
        var button = $(this);
        var input = button.prev('input');
        var mediaUploader = wp.media({
            title: 'Select Chatbot Icon',
            button: { text: 'Use this icon' },
            multiple: false
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            input.val(attachment.url);
        });
        mediaUploader.open();
    });

})( jQuery );