jQuery(document).ready(function($) {
    var $pdfBtn = $('#generate-customer-house-list-pdf');
    if ($pdfBtn.length) {
        $pdfBtn.text('Generate PDF with Images');
    }
    // Agregar botón para PDF sin imágenes
    var $pdfBtnNoImg = $('<button id="generate-customer-house-list-pdf-noimg" type="button" class="button button-primary">Generate PDF (No Images)</button>');
    $pdfBtn.after($pdfBtnNoImg);
    $pdfBtn.on('click', function() {
        var postId = $(this).data('postid');
        var nonce = ClientHouseListPDF.nonce;
        // Redirección para descarga directa
        window.location.href = ClientHouseListPDF.ajax_url + '?action=generate_customer_house_list_pdf&post_id=' + postId + '&nonce=' + nonce;
    });
    $pdfBtnNoImg.on('click', function() {
        var postId = $('#post_ID').val();
        var nonce = $pdfBtn.data('nonce');
        window.open(ajaxurl + '?action=generate_customer_house_list_pdf_noimg&post_id=' + postId + '&nonce=' + nonce, '_blank');
    });
});
