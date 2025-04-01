/**
 * City Club Network - Map View JavaScript
 *
 * Initializes and controls the Google Map display.
 */

// Global variables for map instance and markers
var ccnMapInstance;
var ccnMapMarkers = {}; // Store markers by club ID
var ccnInfoWindows = []; // Store info windows to close others

// Ensure Google Maps API script is loaded before running initMap
// The API script should call this function using `&callback=initMap`
function initMap() {
    console.log("initMap called");

    const mapElement = document.getElementById('ccn-map');
    if (!mapElement) {
        console.error("Map container #ccn-map not found.");
        return;
    }

    // Check if map config data is available
    if (typeof ccn_map_config === 'undefined') {
        console.error("Map configuration data (ccn_map_config) is missing.");
        // Display error message in the map container
        mapElement.innerHTML = '<p style="text-align: center; padding-top: 50px; color: red;">Map configuration is missing. Please check plugin settings.</p>';
        return;
    }

    const mapOptions = {
        center: ccn_map_config.center || { lat: 31.7917, lng: -7.0926 }, // Default center
        zoom: ccn_map_config.zoom || 6, // Default zoom
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        // Add more options as needed: styles, controls customization, etc.
        // Example: Disable default UI for a cleaner look
        // disableDefaultUI: true,
        // zoomControl: true,
        // mapTypeControl: false,
        // scaleControl: true,
        // streetViewControl: false,
        // rotateControl: false,
        // fullscreenControl: true
        styles: [ // Example: Subtle grayscale style
            // { elementType: "geometry", stylers: [{ color: "#f5f5f5" }] },
            // { elementType: "labels.icon", stylers: [{ visibility: "off" }] },
            // { elementType: "labels.text.fill", stylers: [{ color: "#616161" }] },
            // { elementType: "labels.text.stroke", stylers: [{ color: "#f5f5f5" }] },
            // { featureType: "administrative.land_parcel", elementType: "labels.text.fill", stylers: [{ color: "#bdbdbd" }] },
            // { featureType: "poi", elementType: "geometry", stylers: [{ color: "#eeeeee" }] },
            // { featureType: "poi", elementType: "labels.text.fill", stylers: [{ color: "#757575" }] },
            // { featureType: "poi.park", elementType: "geometry", stylers: [{ color: "#e5e5e5" }] },
            // { featureType: "poi.park", elementType: "labels.text.fill", stylers: [{ color: "#9e9e9e" }] },
            // { featureType: "road", elementType: "geometry", stylers: [{ color: "#ffffff" }] },
            // { featureType: "road.arterial", elementType: "labels.text.fill", stylers: [{ color: "#757575" }] },
            // { featureType: "road.highway", elementType: "geometry", stylers: [{ color: "#dadada" }] },
            // { featureType: "road.highway", elementType: "labels.text.fill", stylers: [{ color: "#616161" }] },
            // { featureType: "road.local", elementType: "labels.text.fill", stylers: [{ color: "#9e9e9e" }] },
            // { featureType: "transit.line", elementType: "geometry", stylers: [{ color: "#e5e5e5" }] },
            // { featureType: "transit.station", elementType: "geometry", stylers: [{ color: "#eeeeee" }] },
            // { featureType: "water", elementType: "geometry", stylers: [{ color: "#c9c9c9" }] },
            // { featureType: "water", elementType: "labels.text.fill", stylers: [{ color: "#9e9e9e" }] }
        ]
    };

    // Create the map instance
    try {
        ccnMapInstance = new google.maps.Map(mapElement, mapOptions);
        console.log("Map instance created.");
    } catch (e) {
        console.error("Error creating Google Map instance:", e);
        mapElement.innerHTML = '<p style="text-align: center; padding-top: 50px; color: red;">Could not initialize Google Maps. Check API key and console for errors.</p>';
        return;
    }


    // Add markers to the map
    addMarkers(ccn_map_config.clubs);

    // Optional: Fit map bounds to markers
    fitBoundsToMarkers();

    // Add listener to close info windows when map is clicked
    google.maps.event.addListener(ccnMapInstance, 'click', function() {
        closeAllInfoWindows();
    });

    console.log("Map initialization complete.");
}

// Function to add markers based on club data
function addMarkers(clubs) {
    if (!ccnMapInstance || !clubs || clubs.length === 0) {
        console.log("No map instance or clubs data to add markers.");
        return;
    }

    // Clear existing markers if any (useful for updates)
    clearMarkers();
    ccnMapMarkers = {}; // Reset markers object

    clubs.forEach(club => {
        if (club.lat && club.lng) {
            const markerIconUrl = club.isPremium
                ? (ccn_map_config.marker_icon_premium || '../images/marker-premium.svg') // Use localized or default
                : (ccn_map_config.marker_icon_standard || '../images/marker-standard.svg');

            const marker = new google.maps.Marker({
                position: { lat: club.lat, lng: club.lng },
                map: ccnMapInstance,
                title: club.title,
                icon: {
                    url: markerIconUrl,
                    scaledSize: new google.maps.Size(32, 42), // Adjust size as needed
                    anchor: new google.maps.Point(16, 42) // Anchor point (center bottom)
                },
                // Use custom properties to store data
                clubId: club.id,
                // animation: google.maps.Animation.DROP, // Optional animation
            });

            // Store marker reference
            ccnMapMarkers[club.id] = marker;

            // Create info window content
            const infoWindowContent = `
                <div class="ccn-map-info-window">
                    <h4 class="ccn-map-info-title">${club.title || ''}</h4>
                    ${club.address ? `<p class="ccn-map-info-address">${club.address}</p>` : ''}
                    ${club.permalink ? `<a href="${club.permalink}" target="_blank" class="ccn-map-info-link">View Details</a>` : ''}
                </div>
            `;

            // Create info window instance
            const infoWindow = new google.maps.InfoWindow({
                content: infoWindowContent,
                maxWidth: 280 // Set max width for consistency
            });

            ccnInfoWindows.push(infoWindow); // Store for closing later

            // Add click listener to open info window
            marker.addListener('click', () => {
                closeAllInfoWindows(); // Close others before opening new one
                infoWindow.open({
                    anchor: marker,
                    map: ccnMapInstance,
                    shouldFocus: false // Prevent map panning on open
                });
                // Highlight corresponding sidebar item
                highlightSidebarItem(club.id);
            });
        } else {
            console.warn(`Club "${club.title}" (ID: ${club.id}) is missing coordinates.`);
        }
    });
    console.log(`${Object.keys(ccnMapMarkers).length} markers added.`);
}

// Function to clear all markers from the map
function clearMarkers() {
    for (const id in ccnMapMarkers) {
        if (ccnMapMarkers.hasOwnProperty(id)) {
            ccnMapMarkers[id].setMap(null);
        }
    }
    ccnMapMarkers = {};
    console.log("Existing markers cleared.");
}

// Function to close all open info windows
function closeAllInfoWindows() {
    ccnInfoWindows.forEach(iw => iw.close());
}

// Function to fit map bounds to show all markers
function fitBoundsToMarkers() {
    if (!ccnMapInstance || Object.keys(ccnMapMarkers).length === 0) {
        return; // No map or markers
    }

    const bounds = new google.maps.LatLngBounds();
    for (const id in ccnMapMarkers) {
        if (ccnMapMarkers.hasOwnProperty(id)) {
            bounds.extend(ccnMapMarkers[id].getPosition());
        }
    }

    if (Object.keys(ccnMapMarkers).length > 1) {
        ccnMapInstance.fitBounds(bounds);
        // Add padding if needed: ccnMapInstance.fitBounds(bounds, padding);
    } else if (Object.keys(ccnMapMarkers).length === 1) {
        // If only one marker, center on it and set a reasonable zoom level
        ccnMapInstance.setCenter(bounds.getCenter());
        ccnMapInstance.setZoom(ccn_map_config.zoom_single || 14); // Use configured or default zoom
    }
     // Optional: Add a listener to prevent excessive zoom on fitBounds
    // google.maps.event.addListenerOnce(ccnMapInstance, 'idle', function() {
    //     if (ccnMapInstance.getZoom() > 16) { // Don't zoom in too far
    //         ccnMapInstance.setZoom(16);
    //     }
    // });
    console.log("Map bounds adjusted to markers.");
}

// Function to highlight sidebar item corresponding to marker click
function highlightSidebarItem(clubId) {
    const sidebarItems = document.querySelectorAll('.ccn-map-club-item');
    sidebarItems.forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-club-id') == clubId) { // Use == for type coercion if needed
            item.classList.add('active');
            // Scroll sidebar to show the active item
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
}

// --- Public function for updating map (e.g., after AJAX filter) ---
function updateMap(newClubsData) {
     if (!ccnMapInstance) {
        console.error("Map not initialized, cannot update.");
        return;
    }
    console.log("Updating map with new data:", newClubsData);
    // Update the global config if necessary (or just pass data directly)
    // ccn_map_config.clubs = newClubsData.clubs;
    // ccn_map_config.center = newClubsData.center;
    // ccn_map_config.zoom = newClubsData.zoom;

    addMarkers(newClubsData.clubs); // Clears old markers and adds new ones
    fitBoundsToMarkers(); // Adjust bounds

    // Update sidebar list (this might be handled by the AJAX success callback instead)
    // updateSidebarList(newClubsData.clubs);
}

// Example of how the sidebar list could be updated (if not done via full HTML replacement)
/*
function updateSidebarList(clubs) {
    const listContainer = document.getElementById('ccn-map-clubs-list');
    if (!listContainer) return;

    let listHtml = '';
    if (clubs && clubs.length > 0) {
        clubs.forEach(club => {
            listHtml += `
                <div class="ccn-map-club-item" data-club-id="${club.id}">
                    <div class="ccn-map-club-thumbnail">
                        ${club.thumbnail ? `<img src="${club.thumbnail}" alt="${club.title}" loading="lazy">` : '<div class="ccn-map-club-no-thumbnail"></div>'}
                        ${club.isPremium ? '<span class="ccn-premium-badge">Premium</span>' : ''}
                    </div>
                    <div class="ccn-map-club-info">
                        <h4 class="ccn-map-club-title">${club.title}</h4>
                        ${club.address ? `<p class="ccn-map-club-address"><i class="ccn-location-pin-icon"></i> ${club.address}</p>` : ''}
                        <a href="${club.permalink}" class="ccn-map-club-link" target="_blank">View Details</a>
                    </div>
                </div>
            `;
        });
    } else {
        listHtml = '<div class="ccn-no-clubs-found" style="padding: 20px; text-align: center;"><p>No locations found.</p></div>';
    }
    listContainer.innerHTML = listHtml;

    // Update locations count
    const countElement = document.querySelector('.ccn-locations-count span');
    if (countElement) {
        const count = clubs ? clubs.length : 0;
        countElement.textContent = `${count} Location${count !== 1 ? 's' : ''} Found`;
    }
}
*/

// Note: If using jQuery, replace document.getElementById, querySelectorAll etc. with $() equivalents.
// Example: const mapElement = $('#ccn-map')[0]; // Get DOM element
// Example: const sidebarItems = $('.ccn-map-club-item');
