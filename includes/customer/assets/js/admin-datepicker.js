jQuery(document).ready(function($) {
    // Initialize datepicker for assignment date
    if ($.fn.datepicker) {
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    }
});
