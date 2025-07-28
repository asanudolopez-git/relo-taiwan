(function($){
    $(document).ready(function(){
        var $propertyType = $('#property_type');
        if($propertyType.length){
            $propertyType.select2({
                placeholder: 'Select Property Type',
                width: '100%'
            });
        }
    });
})(jQuery);
