/**
 * Property Search and Filter for Customer House List
 */
jQuery(document).ready(function($) {
    // Desactivar eventos de acordeón en los botones More Info y contenido de detalles
    disableAccordionEventsOnButtons();
    
    // Añadir manejadores de eventos directamente
    setupEventHandlers();
    
    // Prevenir que el acordeón capture eventos de los botones
    function disableAccordionEventsOnButtons() {
        // Eliminar cualquier evento de clic existente en los botones toggle-details
        $('.toggle-details').off('click');
        
        // Detener la propagación de eventos desde los botones y detalles
        $(document).on('mousedown mouseup click', '.toggle-details, .property-details', function(e) {
            e.stopPropagation();
        });
        
        // Asegurarse de que los clics en los botones no afecten al acordeón
        $('.property-item').on('click', function(e) {
            if ($(e.target).closest('.toggle-details, .property-details').length) {
                return false;
            }
        });
    }
    
    // Configurar todos los manejadores de eventos
    function setupEventHandlers() {
        // Mejorar el funcionamiento del botón toggle-details
        $(document).on('click', '.toggle-details', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var propertyId = $(this).data('property-id');
            var $details = $('#property-details-' + propertyId);
            var $button = $(this);
            
            // Cambiar el texto del botón inmediatamente
            var isVisible = $details.is(':visible');
            $button.text(isVisible ? 'More Info' : 'Less Info');
            
            // Mostrar/ocultar los detalles fuera del flujo normal de eventos
            setTimeout(function() {
                $details.slideToggle(300);
            }, 5);
            
            return false;
        });
        
        // Prevenir que los clics dentro de los detalles cierren el acordeón
        $(document).on('click', '.property-details', function(e) {
            e.stopPropagation();
        });
        
        // Search input handler
        $(document).on('input', '#property-search-input', function() {
            var searchTerm = $(this).val().toLowerCase();
            filterProperties(searchTerm);
        });
        
        // Property type filter handler
        $(document).on('change', '#property-type-filter', function() {
            filterProperties();
        });
        
        // MRT line filter handler
        $(document).on('change', '#mrt-line-filter', function() {
            filterProperties();
        });
        
        // Clear filters button
        $(document).on('click', '#clear-property-filters', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#property-search-input').val('');
            $('#property-type-filter').val('');
            $('#mrt-line-filter').val('');
            $('.property-item').show();
            updatePropertyCount();
            return false;
        });
        
        // Select all visible properties
        $(document).on('click', '#select-all-properties', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.property-item:visible input[type="checkbox"]').prop('checked', true);
            updatePropertyCount();
            return false;
        });
        
        // Deselect all properties
        $(document).on('click', '#deselect-all-properties', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.property-item input[type="checkbox"]').prop('checked', false);
            updatePropertyCount();
            return false;
        });
        
        // Show only selected properties
        $(document).on('click', '#show-selected-properties', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.property-item').each(function() {
                var isChecked = $(this).find('input[type="checkbox"]').is(':checked');
                $(this).toggle(isChecked);
            });
            updatePropertyCount();
            return false;
        });
        
        // Show all properties (clear the "show only selected" filter)
        $(document).on('click', '#show-all-properties', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.property-item').show();
            filterProperties();
            return false;
        });
        
        // Initialize property count
        updatePropertyCount();
    }
    
    // Filter properties based on search term and filters
    function filterProperties(searchTerm) {
        // Get current search term if not provided
        if (searchTerm === undefined) {
            searchTerm = $('#property-search-input').val().toLowerCase();
        }
        
        // Get filter values
        var propertyType = $('#property-type-filter').val();
        var mrtLine = $('#mrt-line-filter').val();
        
        // Filter properties
        $('.property-item').each(function() {
            var $item = $(this);
            var title = $item.find('.title-text').text().toLowerCase();
            var type = $item.data('property-type') ? $item.data('property-type').toString().toLowerCase() : '';
            var mrt = $item.data('mrt-line') ? $item.data('mrt-line').toString().toLowerCase() : '';
            var address = $item.data('address') ? $item.data('address').toString().toLowerCase() : '';
            var id = $item.data('property-id') ? $item.data('property-id').toString().toLowerCase() : '';
            
            // Check if property matches search term
            var matchesSearch = searchTerm === '' || 
                title.indexOf(searchTerm) > -1 || 
                address.indexOf(searchTerm) > -1 ||
                id.indexOf(searchTerm) > -1;
            
            // Check if property matches property type filter
            var matchesType = propertyType === '' || type === propertyType;
            
            // Check if property matches MRT line filter
            var matchesMrt = mrtLine === '' || mrt === mrtLine;
            
            // Show/hide property based on filters
            $item.toggle(matchesSearch && matchesType && matchesMrt);
        });
        
        // Update property count
        updatePropertyCount();
    }
    
    // Update the count of visible properties
    function updatePropertyCount() {
        var totalCount = $('.property-item').length;
        var visibleCount = $('.property-item:visible').length;
        var selectedCount = $('.property-item input[type="checkbox"]:checked').length;
        
        $('#property-count').text(visibleCount + ' of ' + totalCount + ' properties shown (' + selectedCount + ' selected)');
    }
    
    // Update property count when checkboxes change
    $(document).on('change', '.property-item input[type="checkbox"]', function() {
        updatePropertyCount();
    });
    
    // Ejecutar updatePropertyCount después de que la página se haya cargado completamente
    $(window).on('load', function() {
        updatePropertyCount();
    });
});
