/**
 * Client Lease Admin JavaScript
 * Handles dynamic loading of properties based on client selection
 */
jQuery(document).ready(function($) {
    const $client_lease_select = $('#client_lease_id');
    $client_lease_select.on('change', function() {
            const titleInput = $('#title');
            const selectedOption = $(this).find('option:selected');
            const clientLeaseName = selectedOption.text();
            if (clientLeaseName && clientLeaseName !== '') {
                titleInput.val(clientLeaseName);
            }
        });
});