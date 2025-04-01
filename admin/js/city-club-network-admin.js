/**
 * All admin-facing JavaScript specific to this plugin.
 *
 * @since      1.0.0
 * @package    City_Club_Network
 */

(function( $ ) {
	'use strict';

    // Function to initialize the Google Map for the location picker
    function initAdminMap() {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            console.warn('CCN Admin: Google Maps API not loaded.');
            return;
        }

        var mapContainer = $('#club-location-map');
        if (!mapContainer.length) {
            // console.log('CCN Admin: Map container not found.');
            return; // Not on the CPT edit screen with the map div
        }

        // Check if API key is actually present (via localized data)
        if (typeof ccn_admin_data === 'undefined' || !ccn_admin_data.google_maps_api_key_present) {
             console.warn('CCN Admin: Google Maps API Key is missing. Map disabled.');
             mapContainer.html('<p style="padding: 10px; text-align: center; color: #888;">Google Maps API Key not configured in settings. Map preview disabled.</p>');
             return;
        }


        var latInput = $('#club_latitude');
        var lngInput = $('#club_longitude');
        var addressInput = $('#club_address'); // Address field for geocoding

        var initialLat = parseFloat(latInput.val()) || 31.7917; // Default to Morocco center approx.
        var initialLng = parseFloat(lngInput.val()) || -7.0926; // Default to Morocco center approx.
        var initialZoom = (latInput.val() && lngInput.val()) ? 15 : 6; // Zoom in if coords exist

        var mapOptions = {
            center: new google.maps.LatLng(initialLat, initialLng),
            zoom: initialZoom,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map(mapContainer[0], mapOptions);

        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(initialLat, initialLng),
            map: map,
            draggable: true // Allow dragging the marker
        });

        // --- Geocoding ---
        var geocoder = new google.maps.Geocoder();

        // Function to geocode address and update map/marker/inputs
        function geocodeAddress() {
            var address = addressInput.val();
            if (!address) return;

            geocoder.geocode({ 'address': address }, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK && results[0]) {
                    var location = results[0].geometry.location;
                    map.setCenter(location);
                    marker.setPosition(location);
                    latInput.val(location.lat().toFixed(6));
                    lngInput.val(location.lng().toFixed(6));
                    map.setZoom(15); // Zoom in after geocoding
                } else {
                    alert('Geocode was not successful for the following reason: ' + status);
                }
            });
        }

        // Add a button or trigger for geocoding (optional)
        // Example: $('<button type="button" class="button" style="margin-left: 10px;">Find on Map</button>')
        //     .insertAfter(addressInput)
        //     .on('click', geocodeAddress);

        // Or trigger geocode when address field loses focus (might be annoying)
        // addressInput.on('blur', geocodeAddress);


        // --- Event Listeners ---

        // Update inputs when marker is dragged
        google.maps.event.addListener(marker, 'dragend', function(event) {
            latInput.val(event.latLng.lat().toFixed(6));
            lngInput.val(event.latLng.lng().toFixed(6));
        });

        // Update marker position if inputs change manually
        function updateMarkerPositionFromInputs() {
            var lat = parseFloat(latInput.val());
            var lng = parseFloat(lngInput.val());
            if (!isNaN(lat) && !isNaN(lng)) {
                var newPos = new google.maps.LatLng(lat, lng);
                // Check if position actually changed to avoid infinite loops if dragend also triggers change
                if (!marker.getPosition().equals(newPos)) {
                    marker.setPosition(newPos);
                    map.setCenter(newPos);
                     // Optionally zoom if map is very zoomed out
                    if (map.getZoom() < 10) {
                        map.setZoom(15);
                    }
                }
            }
        }

        latInput.on('change', updateMarkerPositionFromInputs);
        lngInput.on('change', updateMarkerPositionFromInputs);

         // Add listener for clicking on the map to place marker
        google.maps.event.addListener(map, 'click', function(event) {
            marker.setPosition(event.latLng);
            latInput.val(event.latLng.lat().toFixed(6));
            lngInput.val(event.latLng.lng().toFixed(6));
        });

        console.log('CCN Admin: Map Initialized.');

    } // End initAdminMap


	$(function() { // Document Ready

		// Initialize WordPress Color Picker on settings page
        if ($('.ccn-color-picker').length > 0 && typeof $.fn.wpColorPicker === 'function') {
		    $('.ccn-color-picker').wpColorPicker();
            console.log('CCN Admin: Color Pickers Initialized.');
        }

        // Attempt to initialize the admin map
        // We need to ensure the Google Maps API script has loaded *before* calling this.
        // Since we enqueue Google Maps with no callback, we might need a slight delay or check.
        // A simple check:
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
            initAdminMap();
        } else {
            // If Google Maps isn't loaded yet, wait a moment and try again
            // This is a basic fallback, MutationObserver or a dedicated loader might be better
            setTimeout(initAdminMap, 500);
        }

        // --- Media Uploader for PDF ---
        var mediaUploader;

        $('.ccn-upload-pdf-button').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $urlField = $button.siblings('.ccn-pdf-url-field');
            var $removeButton = $button.siblings('.ccn-remove-pdf-button');

            // If the uploader object has already been created, reopen the dialog
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            // Extend the wp.media object
            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose PDF',
                button: {
                    text: 'Choose PDF'
                },
                multiple: false, // Only allow single file selection
                library: {
                    type: 'application/pdf' // Only show PDF files
                }
            });

            // When a file is selected, grab the URL and set it as the text field's value
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $urlField.val(attachment.url);
                $removeButton.show(); // Show remove button
            });

            // Open the uploader dialog
            mediaUploader.open();
        });

        // Handle Remove PDF button click
        $('.ccn-remove-pdf-button').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $urlField = $button.siblings('.ccn-pdf-url-field');
            $urlField.val(''); // Clear the URL field
            $button.hide(); // Hide the remove button
        });


	}); // End document ready

})( jQuery );
