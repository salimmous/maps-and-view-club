jQuery(document).ready(function($) {
    'use strict';

    // --- Global Variables ---
    const mainContainer = $('#ccn-main-container');
    const gridViewContent = $('.ccn-grid-view-content');
    const mapViewContent = $('.ccn-map-view-content');
    const viewToggleButtons = $('.ccn-view-button');
    const filterForm = $('#ccn-filter-form');
    let isMapLoading = false; // Flag to prevent multiple AJAX calls

    // Check if public data is available
    if (typeof ccn_public_data === 'undefined') {
        console.error('CCN Error: Localization data (ccn_public_data) not found.');
        // Optionally disable view switching or show an error
        return;
    }

    // --- View Toggling Logic ---
    function switchView(targetView) {
        const currentView = mainContainer.attr('data-view');
        if (targetView === currentView) return; // Do nothing if already in the target view

        viewToggleButtons.removeClass('active');
        $(`.ccn-view-button[data-view="${targetView}"]`).addClass('active');
        mainContainer.attr('data-view', targetView);

        if (targetView === 'grid') {
            mapViewContent.hide().removeClass('active');
            gridViewContent.show().addClass('active');
        } else if (targetView === 'map') {
            gridViewContent.hide().removeClass('active');
            mapViewContent.show().addClass('active');
            initializeOrResizeMap(); // Handle map initialization or resize
        }
    }

    // --- Map Initialization/Loading ---
    function initializeOrResizeMap() {
        const mapContainerElement = $('#ccn-map'); // The actual map div inside the partial

        // Case 1: Map container exists and map is initialized -> Resize
        if (mapContainerElement.length > 0 && mapViewContent.data('map-initialized') && typeof ccnMapInstance !== 'undefined' && ccnMapInstance) {
            console.log("CCN: Resizing existing map.");
            google.maps.event.trigger(ccnMapInstance, 'resize');
            // Optional: Recenter or fit bounds if needed after resize
            // fitBoundsToMarkers();
        }
        // Case 2: Map container exists but map NOT initialized (e.g., initial load was map view) -> Initialize
        else if (mapContainerElement.length > 0 && !mapViewContent.data('map-initialized')) {
             if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                 if (typeof ccn_map_config !== 'undefined') { // Check if localized data exists from initial load
                     console.log("CCN: Initializing map from pre-loaded data.");
                     // initMap() should be called by the Google Maps API callback=initMap
                     // We just mark it as initialized here. If initMap hasn't run yet, it will.
                     // If it has run, this flag prevents re-running parts of the logic.
                     mapViewContent.data('map-initialized', true);
                 } else {
                     console.warn("CCN: Map container exists, but map config data missing. Attempting AJAX load.");
                     loadMapViewAjax(); // Fallback to AJAX load if config is missing
                 }
             } else {
                 console.warn("CCN: Google Maps API not ready yet.");
                 // API script with callback=initMap should handle initialization
             }
        }
        // Case 3: Map container DOES NOT exist -> Load via AJAX
        else if (mapContainerElement.length === 0) {
            loadMapViewAjax();
        }
    }

    // --- AJAX Function to Load Map View ---
    function loadMapViewAjax() {
        if (isMapLoading) return; // Prevent simultaneous requests
        isMapLoading = true;

        const loadingPlaceholder = mapViewContent.find('.ccn-map-loading-placeholder');
        loadingPlaceholder.text(ccn_public_data.text?.loading_map || 'Loading Map View...').show();

        // Get current filter values for the AJAX request
        const filters = {
            ccn_city: $('#ccn-city-filter').val(),
            ccn_facility: $('#ccn-facility-filter').val(),
            ccn_membership_category: $('#ccn-membership-filter').val()
        };

        $.ajax({
            url: ccn_public_data.ajax_url,
            type: 'POST', // Use POST for potentially longer filter data
            data: {
                action: 'ccn_load_map_view',
                nonce: ccn_public_data.map_nonce,
                ...filters // Spread filter values into data
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.html && response.data.map_config) {
                    console.log("CCN: Map view loaded via AJAX.");
                    mapViewContent.html(response.data.html); // Inject the map partial HTML

                    // Make map config data available globally for initMap
                    // This assumes initMap will look for this global variable.
                    window.ccn_map_config = response.data.map_config;

                    // Check if Google Maps API is loaded. If yes, initMap should be called by API callback.
                    // If not, the API script needs to be loaded.
                    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                        if (ccn_public_data.google_maps_api_key_present) {
                            console.log("CCN: Google Maps API not loaded, attempting to load script...");
                            // Dynamically load the Google Maps script if necessary
                            // This is complex and might be better handled by ensuring it's always registered
                            // For simplicity, we assume WP's enqueueing handles it eventually.
                            // The callback=initMap in the registered script URL is crucial.
                            // We might need to manually trigger initMap if the callback fails after dynamic load.
                        } else {
                             console.error("CCN: Cannot load map, Google Maps API key missing.");
                             $('#ccn-map').html('<p style="text-align: center; padding-top: 50px; color: red;">Map cannot be loaded. API Key missing.</p>');
                        }
                    } else {
                         // If API is already loaded, the callback=initMap should have fired or will fire.
                         // If it already fired BEFORE our AJAX finished, we might need to call initMap manually.
                         // Check if map instance was created by the callback
                         if (typeof ccnMapInstance === 'undefined') {
                             console.log("CCN: API loaded, but map instance not found. Calling initMap manually.");
                             // Ensure initMap is globally accessible (defined outside jQuery ready)
                             if (typeof initMap === 'function') {
                                 initMap();
                             } else {
                                 console.error("CCN: initMap function is not defined globally.");
                             }
                         }
                    }
                    mapViewContent.data('map-initialized', true); // Mark as initialized
                } else {
                    console.error("CCN AJAX Error (Load Map View):", response.data?.message || 'Unknown error');
                    loadingPlaceholder.text(ccn_public_data.text?.map_load_error || 'Error loading map.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("CCN AJAX Network Error (Load Map View):", textStatus, errorThrown);
                loadingPlaceholder.text(ccn_public_data.text?.map_load_error || 'Error loading map.');
            },
            complete: function() {
                isMapLoading = false;
                // Placeholder might be removed by successful HTML injection, or hide it on error
                // loadingPlaceholder.hide();
            }
        });
    }


    // --- Event Handlers ---

    // View Toggle Buttons
    viewToggleButtons.on('click', function(e) {
        e.preventDefault();
        const targetView = $(this).data('view');
        switchView(targetView);
    });

    // Filter Form Submission (Standard page reload)
    // No AJAX filtering implemented here currently.

    // Pagination Link Clicks (Standard page reload)
    // No AJAX pagination implemented here currently.

    // Map Sidebar Item Click (Interaction with map markers)
    $(document).on('click', '.ccn-map-club-item', function() {
        const clubId = $(this).data('club-id');
        if (!clubId || typeof ccnMapMarkers === 'undefined' || typeof google === 'undefined') return;

        $('.ccn-map-club-item').removeClass('active');
        $(this).addClass('active');

        if (ccnMapMarkers[clubId]) {
            const marker = ccnMapMarkers[clubId];
            if (typeof ccnMapInstance !== 'undefined' && ccnMapInstance) {
                ccnMapInstance.panTo(marker.getPosition());
                // Delay triggering click slightly after pan for smoother experience
                setTimeout(function() {
                    new google.maps.event.trigger(marker, 'click');
                }, 150);
            } else {
                // Fallback if map instance isn't ready (shouldn't happen often)
                new google.maps.event.trigger(marker, 'click');
            }
        } else {
            console.warn("CCN: Marker not found for club ID:", clubId);
        }
    });

    // --- Initial Setup ---
    // Read initial view from data attribute set by PHP
    const initialView = mainContainer.attr('data-view') || 'grid';
    console.log("CCN: Initial view is", initialView);

    // Ensure correct view is displayed on load
    if (initialView === 'map') {
        gridViewContent.hide().removeClass('active');
        mapViewContent.show().addClass('active');
        // Map initialization is handled by the Google Maps API callback=initMap
        // or potentially by initializeOrResizeMap if needed.
        // initializeOrResizeMap(); // Call here to ensure map loads if it's the initial view
    } else {
        mapViewContent.hide().removeClass('active');
        gridViewContent.show().addClass('active');
    }
    // Active button state is set in the PHP template based on initial view.

}); // End jQuery ready
