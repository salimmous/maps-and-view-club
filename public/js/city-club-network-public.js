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

    // --- Map Initialization/Loading (Optimized) ---
    function initializeOrResizeMap() {
        const mapContainerElement = $('#ccn-map'); // The actual map div inside the partial

        // Case 1: Map container exists and map is initialized -> Resize
        if (mapContainerElement.length > 0 && mapViewContent.data('map-initialized') && typeof ccnMapInstance !== 'undefined' && ccnMapInstance) {
            google.maps.event.trigger(ccnMapInstance, 'resize');
            return; // Exit early after resize
        }
        // Case 2: Map container exists but map NOT initialized -> Initialize
        else if (mapContainerElement.length > 0 && !mapViewContent.data('map-initialized')) {
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                if (typeof ccn_map_config !== 'undefined') {
                    // Mark as initialized immediately to prevent duplicate initialization
                    mapViewContent.data('map-initialized', true);
                } else {
                    loadMapViewAjax(); // Fallback to AJAX load if config is missing
                }
            } else if (ccn_public_data.google_maps_api_key_present) {
                // Load Google Maps API directly if not loaded yet
                const script = document.createElement('script');
                script.src = `https://maps.googleapis.com/maps/api/js?key=${ccn_public_data.google_maps_api_key}&libraries=places&callback=initMap`;
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
                mapViewContent.data('map-initialized', true); // Mark as initialized
            }
        }
        // Case 3: Map container DOES NOT exist -> Load via AJAX
        else if (mapContainerElement.length === 0) {
            loadMapViewAjax();
        }
    }

    // --- AJAX Function to Load Map View (Optimized) ---
    function loadMapViewAjax() {
        if (isMapLoading) return; // Prevent simultaneous requests
        isMapLoading = true;

        // Get current filter values for the AJAX request
        const filters = {
            ccn_city: $('#ccn-city-filter').val(),
            ccn_facility: $('#ccn-facility-filter').val(),
            ccn_membership_category: $('#ccn-membership-filter').val()
        };

        $.ajax({
            url: ccn_public_data.ajax_url,
            type: 'POST',
            data: {
                action: 'ccn_load_map_view',
                nonce: ccn_public_data.map_nonce,
                ...filters
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.html && response.data.map_config) {
                    // Remove loading text before injecting HTML
                    mapViewContent.find('.ccn-map-loading-text').remove();
                    
                    // Inject the map partial HTML
                    mapViewContent.html(response.data.html);
                    
                    // Remove loading indicators immediately
                    mapViewContent.find('.ccn-map-loading-text').remove();
                    mapViewContent.find('.ccn-map-list-loading').remove();
                    
                    // Make map config data available globally for initMap
                    window.ccn_map_config = response.data.map_config;

                    // Load or initialize Google Maps
                    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                        if (ccn_public_data.google_maps_api_key_present) {
                            // Dynamically load the Google Maps script
                            const script = document.createElement('script');
                            script.src = `https://maps.googleapis.com/maps/api/js?key=${ccn_public_data.google_maps_api_key}&libraries=places&callback=initMap`;
                            script.async = true;
                            script.defer = true;
                            document.head.appendChild(script);
                        } else {
                            $('#ccn-map').html('<p style="text-align: center; padding-top: 50px; color: red;">Map cannot be loaded. API Key missing.</p>');
                        }
                    } else if (typeof initMap === 'function' && typeof ccnMapInstance === 'undefined') {
                        // Call initMap directly if API is loaded but map not initialized
                        initMap();
                    }
                    
                    mapViewContent.data('map-initialized', true);
                }
            },
            error: function() {
                // Simplified error handling
                $('#ccn-map').html('<p style="text-align: center; padding-top: 50px; color: red;">Error loading map. Please try again.</p>');
            },
            complete: function() {
                isMapLoading = false;
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
