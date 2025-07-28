jQuery(document).ready(function($) {
    // Encontrar los campos de dirección
    let addressField = $('input[id="address"]');
    let chineseAddressField = $('input[id="chinese_address"]');
    
    // Si no encontramos los campos por ID, intentar por nombre
    if (addressField.length === 0) {
        addressField = $('input[name="address"]');
    }
    if (chineseAddressField.length === 0) {
        chineseAddressField = $('input[name="chinese_address"]');
    }
    // Si aún no encontramos los campos, intentar por atributos más generales
    if (addressField.length === 0) {
        addressField = $('input[name$="[address]"]'); // Common in meta boxes
    }
    if (chineseAddressField.length === 0) {
        chineseAddressField = $('input[name$="[chinese_address]"]'); // Common in meta boxes
    }

    let selectedPlaceFromAutocomplete = null; // To store details from Autocomplete selection

    // Helper function to perform translation
    // If 'placeDetail' is provided (from Autocomplete), use its location.
    // Otherwise, 'addressString' (from manual input) will be geocoded first.
    function translateAddress(placeDetail, addressString, geocoder, targetField) {
        if (typeof google === 'undefined' || !google.maps || !google.maps.Geocoder) {
            console.error('Google Maps Geocoder API not available.');
            // alert('Google Maps API not loaded.'); // User feedback can be improved
            return;
        }

        if (placeDetail && placeDetail.geometry && placeDetail.geometry.location) {
            // Use location from Autocomplete selection
            console.log('Translating based on Autocomplete selection:', placeDetail.formatted_address);
            const location = placeDetail.geometry.location;
            geocoder.geocode({ 'location': location, 'language': 'zh-CN' }, function(chineseResults, chineseStatus) {
                if (chineseStatus === google.maps.GeocoderStatus.OK && chineseResults[0]) {
                    targetField.val(chineseResults[0].formatted_address);
                    console.log('Address translated to Chinese (from Autocomplete).');
                } else {
                    console.error('Google Geocoding (Chinese from Autocomplete) failed:', chineseStatus, chineseResults);
                    // alert('Error translating from Autocomplete selection.');
                }
            });
        } else if (addressString) {
            // Geocode the English address string first to get location
            console.log('Translating based on manual input string:', addressString);
            geocoder.geocode({ 'address': addressString, 'language': 'en' }, function(englishResults, englishStatus) {
                if (englishStatus === google.maps.GeocoderStatus.OK && englishResults[0] && englishResults[0].geometry && englishResults[0].geometry.location) {
                    const location = englishResults[0].geometry.location;
                    // Now geocode this location to Chinese
                    geocoder.geocode({ 'location': location, 'language': 'zh-CN' }, function(chineseResults, chineseStatus) {
                        if (chineseStatus === google.maps.GeocoderStatus.OK && chineseResults[0]) {
                            targetField.val(chineseResults[0].formatted_address);
                            console.log('Address translated to Chinese (from manual input).');
                        } else {
                            console.error('Google Geocoding (Chinese from manual input) failed:', chineseStatus, chineseResults);
                            // alert('Error translating manually entered address.');
                        }
                    });
                } else {
                    console.error('Google Geocoding (English for manual input) failed:', englishStatus, englishResults);
                    // alert('Could not find the manually entered English address.');
                }
            });
        } else {
            console.log('No address information to translate.');
        }
    }
    
    // Verificar si encontramos los campos
    if (addressField.length > 0 && chineseAddressField.length > 0) {
        console.log('Address fields found for automatic Google Places translation and Autocomplete.');

        // Initialize Google Places Autocomplete on the address field
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && 
            typeof google.maps.places !== 'undefined' && typeof google.maps.places.Autocomplete !== 'undefined') {
            
            const autocomplete = new google.maps.places.Autocomplete(addressField[0], {
                types: ['address'],
                componentRestrictions: { country: 'TW' } // Restringir a Taiwán
            });

            autocomplete.setFields(['formatted_address', 'geometry', 'name', 'address_components']);
            const geocoder = new google.maps.Geocoder(); // Initialize geocoder once

            autocomplete.addListener('place_changed', function() {
                selectedPlaceFromAutocomplete = autocomplete.getPlace();
                if (selectedPlaceFromAutocomplete && selectedPlaceFromAutocomplete.geometry && selectedPlaceFromAutocomplete.geometry.location) {
                    console.log('Place selected via Autocomplete:', selectedPlaceFromAutocomplete.name, selectedPlaceFromAutocomplete.formatted_address);
                    // A place is selected (or re-selected)
                    translateAddress(selectedPlaceFromAutocomplete, null, geocoder, chineseAddressField);
                } else {
                    console.log('Autocomplete selection cleared or place has no geometry.');
                    selectedPlaceFromAutocomplete = null;
                    // Optionally clear Chinese address if English is cleared/invalid
                    // chineseAddressField.val(''); 
                }
            });
            
            // Clear selectedPlaceFromAutocomplete if the user manually types after selecting a place
            addressField.on('input', function() {
                 // If the current input does not match the formatted address of the selected place, 
                 // assume it's a new manual entry.
                if (selectedPlaceFromAutocomplete && addressField.val() !== selectedPlaceFromAutocomplete.formatted_address) {
                    console.log('Manual input detected after Autocomplete selection. Clearing selected place.');
                    selectedPlaceFromAutocomplete = null;
                    // Translation for manual input will happen on blur
                }
            });

            // Add blur event for manual entries
            addressField.on('blur', function() {
                const englishAddress = addressField.val();
                // Only translate on blur if:
                // 1. No place is currently selected via Autocomplete (selectedPlaceFromAutocomplete is null).
                // 2. There is text in the address field.
                if (!selectedPlaceFromAutocomplete && englishAddress) {
                    console.log('Address field blurred with manual input:', englishAddress);
                    translateAddress(null, englishAddress, geocoder, chineseAddressField);
                } else if (!englishAddress) {
                    // If address field is blurred and empty, optionally clear Chinese address
                    // chineseAddressField.val(''); 
                    console.log('Address field blurred and empty.');
                }
            });

        } else {
            console.warn('Google Maps Places Autocomplete could not be initialized. API might not be loaded or "places" library is missing.');
        }
        
        // Button and its description are removed. Translation is now automatic.

    } else {
        console.error('Address fields (English or Chinese) not found for Google Places translation!');
        console.log('Available input fields by ID/Name:', $('input').map(function() { return this.id || this.name; }).get());
    }

    // Prevent form submission on Enter key in specific input fields
    $('#poststuff').on('keydown', 'input[type="text"], input[type="number"], input[type="email"], input[type="url"], input[type="search"], input[type="tel"]', function(event) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            // Allow Enter key for Google Places Autocomplete suggestions
            // The Autocomplete widget often uses a specific class on its container when suggestions are visible.
            // A common class is 'pac-container'. We check if the event target is inside such a container.
            // This is a heuristic and might need adjustment based on the specific Autocomplete implementation details.
            if ($(event.target).closest('.pac-container').length === 0) {
                console.log('Enter key pressed in an input field. Preventing default form submission.');
                event.preventDefault();
                return false;
            }
        }
    });
});
