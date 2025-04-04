/**
 * City Club Network - Map View JavaScript
 *
 * Initializes Google Map, handles marker/sidebar interactions,
 * and fetches club details for the sidebar via AJAX.
 *
 * @version 1.3.0
 */

// Global variables
var ccnMapInstance;
var ccnMapMarkers = {}; // Store markers by club ID { id: markerInstance }
var ccnInfoWindows = []; // Store info windows to close others
var ccnMapConfigData = typeof ccn_map_config !== 'undefined' ? ccn_map_config : null; // Store localized config
var ccnPublicData = typeof ccn_public_data !== 'undefined' ? ccn_public_data : null; // Store localized public data (for AJAX)
var ccnCurrentlySelectedClubId = null; // Track selected club

// Ensure Google Maps API script calls this function using `&callback=initMap`
function initMap() {
    const mapElement = document.getElementById('ccn-map');
    if (!mapElement) return;

    // Remove any loading text immediately
    const loadingText = mapElement.querySelector('.ccn-map-loading-text');
    if (loadingText) loadingText.remove();
    
    // Quick validation checks
    if (!ccnMapConfigData) {
        mapElement.innerHTML = '<p style="text-align: center; padding-top: 50px; color: red;">Map configuration missing.</p>';
        return;
    }
    
    if (!ccnPublicData || !ccnPublicData.google_maps_api_key_present) {
        mapElement.innerHTML = '<p style="text-align: center; padding-top: 50px; color: red;">Google Maps API Key missing.</p>';
        return;
    }


    const mapOptions = {
        center: ccnMapConfigData.center || { lat: 31.7917, lng: -7.0926 },
        zoom: ccnMapConfigData.zoom || 6,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        // Optimize for performance
        disableDefaultUI: false,
        zoomControl: true,
        mapTypeControl: false,
        scaleControl: true,
        streetViewControl: false,
        rotateControl: false,
        fullscreenControl: false
    };

    // Create the map instance
    try {
        ccnMapInstance = new google.maps.Map(mapElement, mapOptions);
    } catch (e) {
        mapElement.innerHTML = '<p style="text-align: center; padding-top: 50px; color: red;">Could not initialize map.</p>';
        return;
    }

    // Optimize by doing these operations in parallel
    // Add markers first for better perceived performance
    if (ccnMapConfigData.clubs && ccnMapConfigData.clubs.length > 0) {
        addMarkers(ccnMapConfigData.clubs);
        fitBoundsToMarkers();
        populateSidebarList(ccnMapConfigData.clubs);
    }

    // Add listener to close info windows when map is clicked
    google.maps.event.addListener(ccnMapInstance, 'click', function() {
        closeAllInfoWindows();
    });

    // Initialize sidebar details placeholder text
    updateSidebarDetailsPlaceholder();
}

// --- Sidebar Population ---

function populateSidebarList(clubs) {
    const listContainer = document.getElementById('ccn-map-clubs-list');
    if (!listContainer) return;
    
    // Remove any loading indicator immediately
    const loadingIndicator = listContainer.querySelector('.ccn-map-list-loading');
    if (loadingIndicator) loadingIndicator.remove();
    
    // Clear previous content
    listContainer.innerHTML = '';
    
    // Handle empty clubs case
    if (!clubs || clubs.length === 0) {
        listContainer.innerHTML = `<div class="ccn-map-list-no-results"><span>${ccnMapConfigData?.text?.no_clubs_found || 'No locations found.'}</span></div>`;
        return;
    }
    
    // Optimize by using document fragment for better performance
    const fragment = document.createDocumentFragment();
    
    // Generate HTML for each club
    clubs.forEach(club => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'ccn-map-club-item';
        itemDiv.dataset.clubId = club.id;
        
        const iconElement = document.createElement('i');
        iconElement.className = 'ccn-map-club-item-icon';
        
        const titleSpan = document.createElement('span');
        titleSpan.className = 'ccn-map-club-item-title';
        titleSpan.textContent = club.title;
        
        itemDiv.appendChild(iconElement);
        itemDiv.appendChild(titleSpan);
        fragment.appendChild(itemDiv);
    });
    
    // Append all items at once for better performance
    listContainer.appendChild(fragment);
}


// --- Marker Handling ---

function addMarkers(clubs) {
    if (!ccnMapInstance || !clubs || clubs.length === 0) return;

    clearMarkers(); // Clear existing markers first

    // Create markers in batch for better performance
    clubs.forEach(club => {
        if (club.lat && club.lng) {
            const markerIconUrl = club.isPremium
                ? (ccnMapConfigData?.marker_icon_premium || '../images/marker-premium.svg')
                : (ccnMapConfigData?.marker_icon_standard || '../images/marker-standard.svg');

            const marker = new google.maps.Marker({
                position: { lat: club.lat, lng: club.lng },
                map: ccnMapInstance,
                title: club.title,
                icon: {
                    url: markerIconUrl,
                    scaledSize: new google.maps.Size(32, 32),
                    anchor: new google.maps.Point(16, 32)
                },
                clubId: club.id,
                optimized: true // Optimize marker rendering
            });

            ccnMapMarkers[club.id] = marker;

            // Create info window content (simple version)
            const infoWindowContent = `
                <div class="ccn-map-info-window">
                    <h4 class="ccn-map-info-title">${club.title || ''}</h4>
                    ${club.address ? `<p class="ccn-map-info-address">${club.address}</p>` : ''}
                    ${club.permalink ? `<a href="${club.permalink}" target="_blank" class="ccn-map-info-link">View Details</a>` : ''}
                </div>
            `;

            const infoWindow = new google.maps.InfoWindow({
                content: infoWindowContent,
                maxWidth: 250
            });
            ccnInfoWindows.push(infoWindow);

            // Marker click listener
            marker.addListener('click', () => {
                handleSelection(club.id);
                closeAllInfoWindows();
                infoWindow.open(ccnMapInstance, marker);
            });
        }
    });
}

function clearMarkers() {
    for (const id in ccnMapMarkers) {
        if (ccnMapMarkers.hasOwnProperty(id)) {
            ccnMapMarkers[id].setMap(null);
        }
    }
    ccnMapMarkers = {};
    ccnInfoWindows = []; // Also clear info windows array
}

function closeAllInfoWindows() {
    ccnInfoWindows.forEach(iw => iw.close());
}

function fitBoundsToMarkers() {
    if (!ccnMapInstance || Object.keys(ccnMapMarkers).length === 0) return;

    const bounds = new google.maps.LatLngBounds();
    for (const id in ccnMapMarkers) {
        if (ccnMapMarkers.hasOwnProperty(id)) {
            bounds.extend(ccnMapMarkers[id].getPosition());
        }
    }

    if (Object.keys(ccnMapMarkers).length > 1) {
        ccnMapInstance.fitBounds(bounds);
    } else if (Object.keys(ccnMapMarkers).length === 1) {
        ccnMapInstance.setCenter(bounds.getCenter());
        ccnMapInstance.setZoom(ccnMapConfigData?.zoom_single || 14);
    }
}

// --- Sidebar Interaction & Details Loading ---

// Central function to handle selection from map or sidebar
function handleSelection(clubId) {
    if (!clubId || clubId === ccnCurrentlySelectedClubId) return; // Avoid re-selecting same club

    console.log(`CCN: Handling selection for Club ID: ${clubId}`);
    ccnCurrentlySelectedClubId = clubId;

    // 1. Highlight sidebar item
    highlightSidebarItem(clubId);

    // 2. Load details into sidebar via AJAX
    loadClubDetailsIntoSidebar(clubId);

    // 3. Pan map (optional, marker click already centers)
    // if (ccnMapMarkers[clubId]) {
    //     ccnMapInstance.panTo(ccnMapMarkers[clubId].getPosition());
    // }
}

function highlightSidebarItem(clubId) {
    const sidebarItems = document.querySelectorAll('.ccn-map-club-item');
    sidebarItems.forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-club-id') == clubId) { // Use == for coercion just in case
            item.classList.add('active');
            // Scroll item into view if needed
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
}

function clearSidebarSelection() {
     ccnCurrentlySelectedClubId = null;
     const sidebarItems = document.querySelectorAll('.ccn-map-club-item');
     sidebarItems.forEach(item => item.classList.remove('active'));
     updateSidebarDetailsPlaceholder(); // Show placeholder text again
}

function updateSidebarDetailsPlaceholder(message = ccnMapConfigData?.text?.select_club || 'Select a club...') {
    const detailsContainer = document.getElementById('ccn-map-selected-club-details');
    if (!detailsContainer) return;

    // Hide content and error
    detailsContainer.querySelector('.ccn-details-content').style.display = 'none';
    detailsContainer.querySelector('.ccn-details-error').style.display = 'none';

    // Show placeholder with message
    const placeholder = detailsContainer.querySelector('.ccn-details-placeholder');
    placeholder.querySelector('span').textContent = message;
    placeholder.style.display = 'flex';
}

function loadClubDetailsIntoSidebar(clubId) {
    const detailsContainer = document.getElementById('ccn-map-selected-club-details');
    if (!detailsContainer || !ccnPublicData || !ccnPublicData.ajax_url || !ccnPublicData.modal_nonce) {
        updateSidebarDetailsPlaceholder('Error loading details.');
        return;
    }

    const contentDiv = detailsContainer.querySelector('.ccn-details-content');
    const errorDiv = detailsContainer.querySelector('.ccn-details-error');
    const placeholderDiv = detailsContainer.querySelector('.ccn-details-placeholder');

    // Skip loading state for faster perceived performance
    contentDiv.style.display = 'none';
    errorDiv.style.display = 'none';
    placeholderDiv.style.display = 'none';
    
    // Show content immediately with a fade-in effect
    contentDiv.style.opacity = '0';
    contentDiv.style.display = 'block';
    
    $.ajax({
        url: ccnPublicData.ajax_url,
        type: 'POST',
        data: {
            action: 'ccn_get_club_details',
            nonce: ccnPublicData.modal_nonce,
            club_id: clubId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                populateSidebarDetails(response.data);
                contentDiv.style.display = 'block';
            } else {
                errorDiv.querySelector('span').textContent = 'Could not load details.';
                errorDiv.style.display = 'flex';
            }
        },
        error: function() {
            errorDiv.querySelector('span').textContent = 'Could not load details.';
            errorDiv.style.display = 'flex';
        }
    });
}

function populateSidebarDetails(clubData) {
    const container = document.getElementById('ccn-map-selected-club-details');
    if (!container || !clubData) return;

    const content = container.querySelector('.ccn-details-content');

    // Title
    content.querySelector('.ccn-details-title').textContent = clubData.title || '';

    // Address
    const addressEl = content.querySelector('.ccn-details-address span');
    if (clubData.address) { addressEl.textContent = clubData.address; addressEl.parentElement.style.display = 'flex'; }
    else { addressEl.parentElement.style.display = 'none'; }

    // Phone
    const phoneEl = content.querySelector('.ccn-details-phone span');
    if (clubData.contact?.phone) { phoneEl.textContent = clubData.contact.phone; phoneEl.parentElement.style.display = 'flex'; }
    else { phoneEl.parentElement.style.display = 'none'; }

    // Hours
    const hoursContainer = content.querySelector('.ccn-details-hours');
    const hoursMF = hoursContainer.querySelector('.ccn-hours-mf');
    const hoursSat = hoursContainer.querySelector('.ccn-hours-sat');
    const hoursSun = hoursContainer.querySelector('.ccn-hours-sun');
    let hasHours = false;
    if (clubData.hours?.mf) { hoursMF.textContent = `Mon-Fri: ${clubData.hours.mf}`; hoursMF.parentElement.style.display = 'flex'; hasHours = true; } else { hoursMF.parentElement.style.display = 'none'; }
    if (clubData.hours?.sat) { hoursSat.textContent = `Saturday: ${clubData.hours.sat}`; hoursSat.parentElement.style.display = 'flex'; hasHours = true; } else { hoursSat.parentElement.style.display = 'none'; }
    if (clubData.hours?.sun) { hoursSun.textContent = `Sunday: ${clubData.hours.sun}`; hoursSun.parentElement.style.display = 'flex'; hasHours = true; } else { hoursSun.parentElement.style.display = 'none'; }
    hoursContainer.style.display = hasHours ? 'block' : 'none';


    // Amenities
    const amenitiesContainer = content.querySelector('.ccn-details-amenities');
    const amenitiesList = amenitiesContainer.querySelector('.ccn-amenities-list');
    amenitiesList.innerHTML = ''; // Clear previous
    let hasAmenities = false;
    // Combine facilities and potentially classes/memberships as amenities if needed
    if (clubData.facilities && clubData.facilities.length > 0) {
        clubData.facilities.forEach(facility => {
            amenitiesList.insertAdjacentHTML('beforeend', `<li>${facility.name}</li>`);
        });
        hasAmenities = true;
    }
    // Add other amenities if available in clubData (e.g., from description or other fields)
    // Example: if (clubData.has_personal_training) { amenitiesList.insertAdjacentHTML('beforeend', `<li>Personal Training</li>`); hasAmenities = true; }

    amenitiesContainer.style.display = hasAmenities ? 'block' : 'none';


    // Directions Button
    const directionsButton = content.querySelector('.ccn-details-directions-button');
    let directionsUrl = '#';
    if (clubData.latitude && clubData.longitude) {
        directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${clubData.latitude},${clubData.longitude}`;
    } else if (clubData.address) {
        directionsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(clubData.address)}`;
    }

    if (directionsUrl !== '#') {
        directionsButton.href = directionsUrl;
        directionsButton.style.display = 'inline-flex';
    } else {
        directionsButton.style.display = 'none';
    }
}


// --- Event Listeners ---

jQuery(document).ready(function($) {
    // Sidebar item click listener (delegated)
    $('#ccn-map-clubs-list').on('click', '.ccn-map-club-item', function() {
        const clubId = $(this).data('club-id');
        handleSelection(clubId); // Central handler

        // Also trigger map marker click for info window
        if (ccnMapMarkers[clubId]) {
             closeAllInfoWindows(); // Close others first
             new google.maps.event.trigger(ccnMapMarkers[clubId], 'click');
        }
    });

    // Note: Marker click listeners are added in addMarkers function.
});

// --- Initialization ---
// initMap() is called by the Google Maps API callback.
// If the API is already loaded when this script runs (e.g., cached),
// we might need to manually call initMap.
// Check if google.maps is loaded and initMap hasn't run yet (check via map instance)
if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof ccnMapInstance === 'undefined') {
    // A small delay might be needed if the API script is still executing its callback setup
    setTimeout(() => {
        if (typeof ccnMapInstance === 'undefined') { // Double check after delay
             console.log("CCN: Google Maps API loaded, manually calling initMap.");
             initMap();
        }
    }, 100); // Adjust delay if needed
}
