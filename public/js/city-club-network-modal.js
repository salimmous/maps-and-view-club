/**
 * City Club Network - Modal JavaScript
 *
 * Handles the modal popup functionality for club details using AJAX.
 */

jQuery(document).ready(function($) {
    'use strict';

    // Check if ccn_public_data is defined (using the correct object name from localization)
    if (typeof ccn_public_data === 'undefined' || typeof ccn_public_data.modal_data === 'undefined') {
        console.error('CCN Error: Localization data (ccn_public_data.modal_data) not found. Modal cannot function correctly.');
        return; // Stop execution if data is missing
    }
    const ccn_data = ccn_public_data.modal_data; // Use the nested modal_data object

    // --- Modal HTML Structure (Append only once) ---
    if ($('.ccn-modal-overlay').length === 0) {
        // Ensure text variables exist even if localization fails partially
        const text = ccn_data.text || {};
        const loadingText = text.loading || 'Loading...';
        const errorText = text.error || 'Could not load club details. Please try again.';
        const noFacilitiesText = text.no_facilities || 'No specific facilities listed.';
        const noClassesText = text.no_classes || 'Class schedule not available.';
        const noMembershipsText = text.no_memberships || 'Membership information not available.';
        const bookTourText = text.book_tour || 'Book a Tour';
        const getDirectionsText = text.get_directions || 'Get Directions';
        const choosePlanText = text.choose_plan || 'Choose Plan';
        const viewPdfText = text.view_pdf || 'View Schedule PDF';
        const downloadPdfText = text.download_pdf || 'Download Schedule PDF';


        const modalHTML = `
            <div class="ccn-modal-overlay">
                <div class="ccn-modal">
                    <div class="ccn-modal-close"></div>
                    <div class="ccn-modal-loading"><div class="ccn-spinner"></div><span>${loadingText}</span></div>
                    <div class="ccn-modal-error"><span>${errorText}</span></div>
                    <div class="ccn-modal-header">
                        <img src="" alt="" class="ccn-modal-header-image">
                        <div class="ccn-modal-premium-badge">Premium Location</div>
                        <div class="ccn-modal-header-content">
                            <h2 class="ccn-modal-title"></h2>
                            <div class="ccn-modal-address"><i></i> <span></span></div>
                        </div>
                    </div>
                    <div class="ccn-modal-tabs">
                        <div class="ccn-modal-tab" data-tab="overview">Overview</div>
                        <div class="ccn-modal-tab" data-tab="facilities">Facilities</div>
                        <div class="ccn-modal-tab" data-tab="classes">Classes</div>
                        <div class="ccn-modal-tab" data-tab="membership">Membership</div>
                    </div>
                    <div class="ccn-modal-content">
                        <!-- Overview Tab -->
                        <div class="ccn-modal-tab-content" data-tab="overview">
                            <div class="ccn-club-info">
                                <div class="ccn-club-info-section ccn-hours-section">
                                    <h3 class="ccn-club-info-title"><i class="ccn-hours-icon"></i> Hours</h3>
                                    <ul class="ccn-club-hours-list"></ul>
                                </div>
                                <div class="ccn-club-info-section ccn-rating-section">
                                    <h3 class="ccn-club-info-title ccn-rating-title"><i></i> Rating</h3>
                                    <div class="ccn-club-rating-display">
                                        <div class="ccn-club-rating-stars"></div>
                                        <span class="ccn-club-rating-value"></span>
                                        <span class="ccn-club-reviews-count"></span>
                                    </div>
                                </div>
                                <div class="ccn-club-info-section ccn-contact-section">
                                    <h3 class="ccn-club-info-title ccn-contact-title"><i></i> Contact</h3>
                                    <ul class="ccn-club-contact-list"></ul>
                                </div>
                            </div>
                            <div class="ccn-club-description"></div>
                        </div>

                        <!-- Facilities Tab -->
                        <div class="ccn-modal-tab-content" data-tab="facilities">
                            <p class="ccn-tab-intro">Explore our premium facilities designed to enhance your fitness experience.</p>
                            <div class="ccn-facilities-list"></div>
                            <p class="ccn-no-data-message" style="display: none;">${noFacilitiesText}</p>
                        </div>

                        <!-- Classes Tab -->
                        <div class="ccn-modal-tab-content" data-tab="classes">
                            <p class="ccn-tab-intro">Join our diverse range of fitness classes led by expert instructors.</p>
                            <div class="ccn-classes-list"></div>
                             <p class="ccn-no-data-message" style="display: none;">${noClassesText}</p>
                             <div class="ccn-class-schedule-actions"> <!-- Container for PDF buttons -->
                                 <a href="#" class="ccn-modal-action-button ccn-view-pdf-button" target="_blank" style="display: none;">${viewPdfText}</a>
                                 <a href="#" class="ccn-modal-action-button ccn-download-pdf-button" download style="display: none;">${downloadPdfText}</a>
                             </div>
                        </div>

                        <!-- Membership Tab -->
                        <div class="ccn-modal-tab-content" data-tab="membership">
                            <p class="ccn-tab-intro">Choose the perfect membership plan for your fitness journey.</p>
                            <div class="ccn-membership-plans"></div>
                             <p class="ccn-no-data-message" style="display: none;">${noMembershipsText}</p>
                        </div>
                    </div>
                    <div class="ccn-modal-footer">
                        <a href="#" class="ccn-modal-action-button ccn-book-tour-button" target="_blank">${bookTourText}</a>
                        <a href="#" class="ccn-modal-action-button ccn-get-directions-button" target="_blank">${getDirectionsText}</a>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modalHTML);
    }

    // --- Modal Element References ---
    const modalOverlay = $('.ccn-modal-overlay');
    const modal = $('.ccn-modal');
    const modalClose = $('.ccn-modal-close');
    const modalTabsContainer = $('.ccn-modal-tabs'); // Reference to the tabs container
    const modalTabs = $('.ccn-modal-tab');
    const modalTabContents = $('.ccn-modal-tab-content');
    const modalLoading = $('.ccn-modal-loading');
    const modalError = $('.ccn-modal-error');
    const modalFooter = $('.ccn-modal-footer'); // Reference to the footer

    // --- Helper Functions ---

    /**
     * Populates the rating stars based on a numeric rating.
     * @param {jQuery} container - The jQuery element to append stars to.
     * @param {number} rating - The rating value (0-5).
     */
    function populateRatingStars(container, rating) {
        container.empty();
        const ratingVal = parseFloat(rating);
        if (isNaN(ratingVal) || ratingVal <= 0) {
            // container.append('<span>No rating available</span>'); // Keep it empty if no rating
            return;
        }

        for (let i = 1; i <= 5; i++) {
            let starClass = 'empty';
            if (ratingVal >= i) {
                starClass = 'filled';
            } else if (ratingVal > i - 1) {
                const decimal = ratingVal - (i - 1);
                 if (decimal >= 0.75) {
                     starClass = 'filled';
                 } else if (decimal >= 0.25) {
                     starClass = 'half-filled';
                 } else {
                     starClass = 'empty';
                 }
            }
            container.append(`<i class="ccn-star-icon ${starClass}"></i>`);
        }
    }

    /**
     * Clears all dynamic content from the modal before loading new data.
     */
    function clearModalContent() {
        // Clear Header
        $('.ccn-modal-title').html(''); // Use .html('') to clear
        $('.ccn-modal-address span').text('');
        $('.ccn-modal-header-image').attr('src', '').attr('alt', '');
        $('.ccn-modal-premium-badge').hide();

        // Clear Overview Content
        $('.ccn-club-hours-list').empty();
        $('.ccn-club-rating-stars').empty();
        $('.ccn-club-rating-value').text('');
        $('.ccn-club-reviews-count').text('');
        $('.ccn-club-contact-list').empty();
        $('.ccn-club-description').empty();

        // Clear Other Tab Content
        $('.ccn-facilities-list').empty();
        $('.ccn-classes-list').empty();
        $('.ccn-membership-plans').empty();
        $('.ccn-view-pdf-button').hide().attr('href', '#'); // Hide and reset PDF buttons
        $('.ccn-download-pdf-button').hide().attr('href', '#');

        // Reset Footer Buttons
        $('.ccn-book-tour-button').attr('href', '#').hide(); // Hide initially
        $('.ccn-get-directions-button').attr('href', '#').hide(); // Hide initially

        // Hide "no data" messages
        $('.ccn-no-data-message').hide();

        // Reset Tabs state (remove active class, but don't hide them yet)
        modalTabs.removeClass('active').show(); // Ensure all tabs are potentially visible initially
        modalTabContents.removeClass('active').hide(); // Hide all content panels initially

        // Ensure containers are visible
        modalTabsContainer.show();
        modalFooter.show();
    }

    /**
     * Opens the modal and populates it with fetched club data.
     * @param {object} clubData - The complete club data object from AJAX.
     */
    function openModal(clubData) {
        clearModalContent(); // Clear previous data first

        // --- Populate Header ---
        $('.ccn-modal-title').html(clubData.title || 'Club Details'); // Use .html() to decode entities
        $('.ccn-modal-address span').text(clubData.address || 'Address not available');
        $('.ccn-modal-header-image')
            .attr('src', clubData.thumbnail || ccn_data.default_image || '') // Add fallback for default image
            .attr('alt', clubData.title || 'Club Image');
        $('.ccn-modal-premium-badge').toggle(!!clubData.is_premium); // Use toggle

        // --- Populate Overview Tab ---
        const overviewTabContent = $('.ccn-modal-tab-content[data-tab="overview"]');
        const hoursList = overviewTabContent.find('.ccn-club-hours-list');
        let hasHours = false;
        if (clubData.hours?.mf) { hoursList.append(`<li><span class="ccn-club-hours-day">Monday - Friday</span><span class="ccn-club-hours-time">${clubData.hours.mf}</span></li>`); hasHours = true; }
        if (clubData.hours?.sat) { hoursList.append(`<li><span class="ccn-club-hours-day">Saturday</span><span class="ccn-club-hours-time">${clubData.hours.sat}</span></li>`); hasHours = true; }
        if (clubData.hours?.sun) { hoursList.append(`<li><span class="ccn-club-hours-day">Sunday</span><span class="ccn-club-hours-time">${clubData.hours.sun}</span></li>`); hasHours = true; }
        overviewTabContent.find('.ccn-hours-section').toggle(hasHours); // Show/hide hours section

        const ratingStars = overviewTabContent.find('.ccn-club-rating-stars');
        const ratingValue = overviewTabContent.find('.ccn-club-rating-value');
        const reviewsCount = overviewTabContent.find('.ccn-club-reviews-count');
        let hasRating = false;
        if (clubData.rating && parseFloat(clubData.rating) > 0) {
            populateRatingStars(ratingStars, clubData.rating);
            ratingValue.text(parseFloat(clubData.rating).toFixed(1));
            reviewsCount.text(clubData.reviews_count ? `(${clubData.reviews_count} reviews)` : '');
            hasRating = true;
        } else {
             ratingStars.empty();
             ratingValue.text('');
             reviewsCount.text('');
        }
        overviewTabContent.find('.ccn-rating-section').toggle(hasRating); // Show/hide rating section

        const contactList = overviewTabContent.find('.ccn-club-contact-list');
        let hasContact = false;
        if (clubData.contact?.phone) { contactList.append(`<li class="ccn-club-contact-phone"><i></i> <span>${clubData.contact.phone}</span></li>`); hasContact = true; }
        if (clubData.contact?.email) { contactList.append(`<li class="ccn-club-contact-email"><i></i> <a href="mailto:${clubData.contact.email}">${clubData.contact.email}</a></li>`); hasContact = true; }
        if (clubData.contact?.website) { contactList.append(`<li class="ccn-club-contact-website"><i></i> <a href="${clubData.contact.website}" target="_blank" rel="noopener noreferrer">${clubData.contact.website.replace(/^https?:\/\//, '')}</a></li>`); hasContact = true; }
        overviewTabContent.find('.ccn-contact-section').toggle(hasContact); // Show/hide contact section

        overviewTabContent.find('.ccn-club-description').html(clubData.description || ''); // Use html() if description contains HTML

        // --- Populate Facilities Tab ---
        const facilitiesTab = $('.ccn-modal-tab[data-tab="facilities"]');
        const facilitiesContent = $('.ccn-modal-tab-content[data-tab="facilities"]');
        const facilitiesList = facilitiesContent.find('.ccn-facilities-list');
        const hasFacilities = clubData.facilities && clubData.facilities.length > 0;
        facilitiesTab.toggle(hasFacilities); // Show/hide tab
        facilitiesContent.find('.ccn-tab-intro').toggle(hasFacilities);
        facilitiesContent.find('.ccn-no-data-message').toggle(!hasFacilities);
        if (hasFacilities) {
            clubData.facilities.forEach(facility => {
                const iconStyle = facility.icon_url ? `style="background-image: url('${facility.icon_url}')"` : '';
                facilitiesList.append(`
                    <div class="ccn-facility-item">
                        <div class="ccn-facility-icon" ${iconStyle}></div>
                        <div class="ccn-facility-details">
                            <div class="ccn-facility-name">${facility.name}</div>
                            <div class="ccn-facility-description">${facility.description || ''}</div>
                        </div>
                    </div>
                `);
            });
        }

        // --- Populate Classes Tab ---
        const classesTab = $('.ccn-modal-tab[data-tab="classes"]');
        const classesContent = $('.ccn-modal-tab-content[data-tab="classes"]');
        const classesList = classesContent.find('.ccn-classes-list');
        const hasClasses = clubData.classes && clubData.classes.length > 0;
        classesTab.toggle(hasClasses || !!clubData.urls?.class_schedule_pdf); // Show tab if classes OR PDF exist
        classesContent.find('.ccn-tab-intro').toggle(hasClasses || !!clubData.urls?.class_schedule_pdf);
        classesContent.find('.ccn-no-data-message').toggle(!hasClasses && !clubData.urls?.class_schedule_pdf); // Show 'no data' only if NO classes AND NO PDF

        if (hasClasses) {
            clubData.classes.forEach(classItem => {
                let levelClass = classItem.level ? classItem.level.toLowerCase().replace(/\s+/g, '-') : 'all-levels';
                classesList.append(`
                    <div class="ccn-class-item">
                        <div class="ccn-class-header">
                            <div class="ccn-class-name">${classItem.name || ''}</div>
                            <div class="ccn-class-level ${levelClass}">${classItem.level || ''}</div>
                        </div>
                        <div class="ccn-class-schedule">${classItem.schedule || ''}</div>
                        <div class="ccn-class-instructor"><i></i> Instructor: ${classItem.instructor || ''}</div>
                    </div>
                `);
            });
        }

        // Show PDF buttons if URL exists
        const pdfUrl = clubData.urls?.class_schedule_pdf;
        const viewPdfButton = classesContent.find('.ccn-view-pdf-button');
        const downloadPdfButton = classesContent.find('.ccn-download-pdf-button');
        if (pdfUrl) {
            viewPdfButton.attr('href', pdfUrl).show();
            downloadPdfButton.attr('href', pdfUrl).show();
        } else {
            viewPdfButton.hide();
            downloadPdfButton.hide();
        }


        // --- Populate Membership Tab ---
        const membershipTab = $('.ccn-modal-tab[data-tab="membership"]');
        const membershipContent = $('.ccn-modal-tab-content[data-tab="membership"]');
        const membershipList = membershipContent.find('.ccn-membership-plans');
        const hasMemberships = clubData.memberships && clubData.memberships.length > 0;
        membershipTab.toggle(hasMemberships); // Show/hide tab
        membershipContent.find('.ccn-tab-intro').toggle(hasMemberships);
        membershipContent.find('.ccn-no-data-message').toggle(!hasMemberships);
        if (hasMemberships) {
            clubData.memberships.forEach(plan => {
                let popularHTML = plan.is_popular ? '<div class="ccn-popular-badge">Most Popular</div>' : '';
                let popularClass = plan.is_popular ? 'popular' : '';
                let featuresHTML = plan.features && Array.isArray(plan.features) ? plan.features.map(feature => `<li>${feature}</li>`).join('') : '';
                const choosePlanText = ccn_data.text?.choose_plan || 'Choose Plan'; // Safe access

                membershipList.append(`
                    <div class="ccn-membership-plan ${popularClass}">
                        ${popularHTML}
                        <div class="ccn-membership-header">
                            <div class="ccn-membership-name">${plan.name || ''}</div>
                            <div class="ccn-membership-price">${plan.price || ''}</div>
                            <div class="ccn-membership-period">${plan.period || ''}</div>
                        </div>
                        <div class="ccn-membership-features">
                            <ul class="ccn-membership-feature-list">
                                ${featuresHTML}
                            </ul>
                            <a href="${plan.url || '#'}" class="ccn-choose-plan-button" target="_blank" rel="noopener noreferrer">${choosePlanText}</a>
                        </div>
                    </div>
                `);
            });
        }

        // --- Populate Footer ---
        const bookTourButton = $('.ccn-book-tour-button');
        const getDirectionsButton = $('.ccn-get-directions-button');

        if (clubData.urls?.book_tour) {
            bookTourButton.attr('href', clubData.urls.book_tour).show();
        } else {
            bookTourButton.hide();
        }

        let directionsUrl = '#';
        if (clubData.latitude && clubData.longitude) {
            directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${clubData.latitude},${clubData.longitude}`;
        } else if (clubData.address) {
            directionsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(clubData.address)}`;
        }

        if (directionsUrl !== '#') {
            getDirectionsButton.attr('href', directionsUrl).show();
        } else {
            getDirectionsButton.hide();
        }

        // --- Show Modal & Set Active Tab ---
        modalLoading.hide();
        modalError.hide();
        modalOverlay.addClass('active');
        $('body').css('overflow', 'hidden'); // Prevent body scrolling

        // Activate the first *visible* tab (should always be Overview if others are hidden)
        modalTabs.removeClass('active'); // Deactivate all first
        modalTabContents.removeClass('active').hide(); // Hide all content panels

        const firstVisibleTab = modalTabs.filter(':visible').first();
        if (firstVisibleTab.length) {
            const activeTabName = firstVisibleTab.data('tab');
            firstVisibleTab.addClass('active');
            $(`.ccn-modal-tab-content[data-tab="${activeTabName}"]`).addClass('active').show();
        } else {
            // Fallback: Should not happen if Overview tab is always visible, but just in case
            $('.ccn-modal-tab[data-tab="overview"]').addClass('active').show();
            overviewTabContent.addClass('active').show();
        }
    }

    /**
     * Closes the modal.
     */
    function closeModal() {
        modalOverlay.removeClass('active');
        $('body').css('overflow', ''); // Allow body scrolling again
    }

    // --- Event Handlers ---

    // Tab switching
    modalTabsContainer.on('click', '.ccn-modal-tab', function() { // Delegate click to container
        const $thisTab = $(this);
        if ($thisTab.hasClass('active') || !$thisTab.is(':visible')) { // Also check if tab is visible
            return; // Do nothing if already active or hidden
        }
        const tab = $thisTab.data('tab');

        modalTabs.removeClass('active');
        modalTabContents.removeClass('active').hide(); // Hide all content

        $thisTab.addClass('active');
        $(`.ccn-modal-tab-content[data-tab="${tab}"]`).addClass('active').show(); // Show selected content
    });

    // Close modal triggers
    modalClose.on('click', closeModal);
    modalOverlay.on('click', function(e) {
        if ($(e.target).is(modalOverlay)) {
            closeModal();
        }
    });
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay.hasClass('active')) {
            closeModal();
        }
    });

    // --- AJAX Trigger ---
    // Intercept View Details button clicks (delegated)
    // Use a more specific selector to avoid conflicts if needed
    $(document).on('click', '.ccn-view-details-button', function(e) {
        e.preventDefault(); // *** ADDED: Prevent default link behavior ***

        // Find the closest parent card OR rely on the button's data attribute directly
        const clubId = $(this).data('club-id');

        if (!clubId) {
            // Try finding from parent card as fallback
            const clubCard = $(this).closest('.ccn-club-card');
            const cardClubId = clubCard.data('club-id');
            if (!cardClubId) {
                console.error('CCN Error: Club ID not found.');
                // Optionally show user feedback here
                return;
            }
             triggerModalAjax(cardClubId);
        } else {
             triggerModalAjax(clubId);
        }
    });

    function triggerModalAjax(clubId) {
         // Show loading state immediately
        modalError.hide(); // Hide previous errors
        modalLoading.show();
        // Don't clear content here, clearModalContent is called in openModal
        modalOverlay.addClass('active'); // Show overlay with loader
        $('body').css('overflow', 'hidden');

        // Perform AJAX request
        $.ajax({
            url: ccn_public_data.ajax_url, // Use the main public data object for ajax_url
            type: 'POST',
            data: {
                action: 'ccn_get_club_details',
                nonce: ccn_public_data.modal_nonce, // Use the main public data object for nonce
                club_id: clubId
            },
            dataType: 'json', // Expect JSON response
            success: function(response) {
                if (response.success && response.data) {
                    openModal(response.data); // Populate and show modal content
                } else {
                    console.error('CCN AJAX Error:', response.data?.message || 'Unknown error');
                    modalLoading.hide();
                    modalError.find('span').text(response.data?.message || ccn_data.text?.error || 'Could not load details.');
                    modalError.show(); // Show error message inside modal
                    // Keep overlay active so user sees the error
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('CCN AJAX Network Error:', textStatus, errorThrown);
                modalLoading.hide();
                modalError.find('span').text(ccn_data.text?.error || 'Could not load details.');
                modalError.show(); // Show error message inside modal
                 // Keep overlay active so user sees the error
            }
        });
    }

}); // End jQuery ready
