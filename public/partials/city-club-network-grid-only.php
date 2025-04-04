<?php
/**
 * Template for displaying the City Club Network in grid-only view with filters.
 * Updated structure to move title/address below image.
 *
 * @since      1.0.0
 * @package    City_Club_Network
 */

// Ensure we have the necessary data
if (!isset($clubs_paged) || !isset($available_filters)) {
    echo '<p>Error: Required data not available.</p>';
    return;
}
?>

<div class="ccn-container" id="ccn-grid-only-container">
    <?php if ($atts['show_title'] === 'yes') : ?>
    <div class="ccn-header">
        <h2 class="ccn-title"><?php echo wp_kses_post($atts['title']); ?></h2>
        <p class="ccn-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
    </div>
    <?php endif; ?>

    <div class="ccn-filter-container">
        <form id="ccn-filter-form" method="GET" action="<?php echo esc_url(get_permalink()); ?>">
            <div class="ccn-filter-bar">
                <div class="ccn-filter-label">
                    <i></i> <!-- Filter icon added via CSS -->
                    <span>Filter by:</span>
                </div>

                <div class="ccn-filter-selects">
                    <div class="ccn-filter-select">
                        <select id="ccn-city-filter" name="ccn_city">
                            <option value="">City</option>
                            <?php if (!empty($available_filters['cities'])) : ?>
                                <?php foreach ($available_filters['cities'] as $city) : ?>
                                    <option value="<?php echo esc_attr($city['slug']); ?>" <?php selected($filters['city'], $city['slug']); ?>>
                                        <?php echo esc_html($city['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="ccn-filter-select">
                        <select id="ccn-facility-filter" name="ccn_facility">
                            <option value="">Facilities</option>
                            <?php if (!empty($available_filters['facilities'])) : ?>
                                <?php foreach ($available_filters['facilities'] as $facility) : ?>
                                    <option value="<?php echo esc_attr($facility['slug']); ?>" <?php selected($filters['facility'], $facility['slug']); ?>>
                                        <?php echo esc_html($facility['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="ccn-filter-select">
                        <select id="ccn-membership-filter" name="ccn_membership_category">
                            <option value="">Membership</option>
                            <?php if (!empty($available_filters['membership_categories'])) : ?>
                                <?php foreach ($available_filters['membership_categories'] as $membership) : ?>
                                    <option value="<?php echo esc_attr($membership['slug']); ?>" <?php selected($filters['membership_category'], $membership['slug']); ?>>
                                        <?php echo esc_html($membership['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" id="ccn-apply-filters" class="ccn-apply-filters-button">
                    <i class="ccn-filter-apply-icon"></i> <!-- Check icon added via CSS -->
                    Apply Filters
                </button>
            </div>
            <!-- Hidden fields for preserving other query parameters if needed -->
            <?php
            foreach ($_GET as $key => $value) {
                // Preserve all GET params except the ones used by our filters and pagination
                if (!in_array($key, ['ccn_city', 'ccn_facility', 'ccn_membership_category', 'paged'])) {
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr(sanitize_text_field($value)) . '" />';
                }
            }
            ?>
        </form>
    </div>

    <!-- Grid Content Area -->
    <div class="ccn-content-area ccn-grid-only-content">
        <?php if (!empty($clubs_paged)) : ?>
            <div class="ccn-clubs-grid">
                <?php foreach ($clubs_paged as $club) : ?>
                    <div class="ccn-club-card" data-club-id="<?php echo esc_attr($club['id']); ?>">
                        <div class="ccn-club-image">
                            <?php if (!empty($club['thumbnail'])) : ?>
                                <img src="<?php echo esc_url($club['thumbnail']); ?>" alt="<?php echo esc_attr($club['title']); ?>" loading="lazy">
                            <?php else : ?>
                                <img src="<?php echo esc_url(CCN_PLUGIN_URL . 'public/images/default-club-image.jpg'); ?>" alt="<?php echo esc_attr($club['title']); ?>" loading="lazy">
                            <?php endif; ?>

                            <?php if (!empty($club['is_premium'])) : ?>
                                <div class="ccn-premium-badge">PREMIUM</div>
                            <?php endif; ?>

                            <!-- Overlay Removed -->
                        </div>

                        <div class="ccn-club-content">
                             <!-- Title and Address Moved Here -->
                            <h3 class="ccn-club-title">
                                <?php echo esc_html($club['title']); ?>
                            </h3>
                            <?php if (!empty($club['address'])) : ?>
                                <div class="ccn-club-address">
                                    <i class="ccn-location-icon"></i>
                                    <span><?php echo esc_html($club['address']); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Facility Tags -->
                            <?php if (!empty($club['facilities'])) : ?>
                                <div class="ccn-club-facilities">
                                    <?php foreach ($club['facilities'] as $facility) : ?>
                                        <div class="ccn-facility-tag" title="<?php echo esc_attr($facility['description'] ?: $facility['name']); ?>">
                                            <?php
                                            // Attempt to generate icon class based on slug or use provided URL
                                            $icon_html = '';
                                            if (!empty($facility['icon_url'])) {
                                                $icon_html = '<i class="ccn-facility-icon" style="background-image: url(\'' . esc_url($facility['icon_url']) . '\');"></i>';
                                            } else {
                                                $icon_slug = sanitize_title($facility['slug'] ?: 'default');
                                                $icon_class = 'ccn-facility-icon-' . $icon_slug; // e.g., ccn-facility-icon-pool
                                                $icon_html = '<i class="ccn-facility-icon ' . esc_attr($icon_class) . '"></i>';
                                            }
                                            echo $icon_html; // Output the icon
                                            ?>
                                            <span><?php echo esc_html($facility['name']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Info Bar (Hours & Rating) -->
                            <div class="ccn-club-info-bar">
                                <?php if (!empty($club['hours']['mf'])) : // Display first available hour string ?>
                                    <div class="ccn-club-hours">
                                        <i class="ccn-clock-icon"></i>
                                        <span><?php echo esc_html($club['hours']['mf']); ?></span>
                                    </div>
                                <?php elseif (!empty($club['hours']['sat'])) : ?>
                                     <div class="ccn-club-hours">
                                        <i class="ccn-clock-icon"></i>
                                        <span><?php echo esc_html($club['hours']['sat']); ?></span>
                                    </div>
                                <?php elseif (!empty($club['hours']['sun'])) : ?>
                                     <div class="ccn-club-hours">
                                        <i class="ccn-clock-icon"></i>
                                        <span><?php echo esc_html($club['hours']['sun']); ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="ccn-club-hours"></div> <?php // Empty div for spacing ?>
                                <?php endif; ?>


                                <?php if (isset($club['rating']) && $club['rating'] > 0) : ?>
                                    <div class="ccn-club-rating">
                                        <i class="ccn-star-icon filled"></i>
                                        <span class="ccn-rating-value"><?php echo esc_html(number_format((float)$club['rating'], 1)); ?></span>
                                        <?php if (!empty($club['reviews_count'])) : ?>
                                            <span class="ccn-reviews-count">(<?php echo esc_html($club['reviews_count']); ?> reviews)</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                     <div class="ccn-club-rating"></div> <?php // Empty div for spacing ?>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="ccn-club-actions">
                                <a href="#" class="ccn-view-details-button" data-club-id="<?php echo esc_attr($club['id']); ?>">
                                    View Details
                                </a>
                                <?php
                                $directions_url = '#';
                                if (!empty($club['latitude']) && !empty($club['longitude'])) {
                                    $directions_url = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($club['latitude'] . ',' . $club['longitude']);
                                } elseif (!empty($club['address'])) {
                                    $directions_url = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($club['address']);
                                }
                                ?>
                                <?php if ($directions_url !== '#') : ?>
                                <a href="<?php echo esc_url($directions_url); ?>" target="_blank" rel="noopener noreferrer" class="ccn-get-directions-button">
                                    Get Directions
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1) : ?>
                <div class="ccn-pagination">
                    <?php
                    // Using WordPress core pagination function for better compatibility
                    echo paginate_links( array(
                        'base'      => $base_url . '%_%',
                        'format'    => (strpos($base_url, '?') ? '&' : '?') . 'paged=%#%',
                        'current'   => max( 1, $current_page ),
                        'total'     => $total_pages,
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'type'      => 'list', // Output as <ul> list
                        'add_args'  => false, // Don't add query args automatically
                    ) );
                    ?>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="ccn-no-clubs-found">
                <p>No clubs found matching your criteria. Please try different filters.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
