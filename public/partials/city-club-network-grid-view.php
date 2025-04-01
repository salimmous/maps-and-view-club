<?php
/**
 * Template for displaying the City Club Network in grid view.
 * Updated to match the design in "image 1".
 *
 * @since      1.0.0
 * @package    City_Club_Network
 */

// Ensure $plugin_instance is available (passed from shortcode)
if (!isset($plugin_instance)) {
    echo '<p>Error: Plugin context not available.</p>';
    return;
}
$this_ref = $plugin_instance; // Use the passed instance

// Get the clubs data using the reference, applying filters from GET parameters or shortcode atts
$filters = array(
    'city' => isset($_GET['ccn_city']) ? sanitize_text_field($_GET['ccn_city']) : (isset($atts['city']) ? $atts['city'] : ''),
    'facility' => isset($_GET['ccn_facility']) ? sanitize_text_field($_GET['ccn_facility']) : (isset($atts['facility']) ? $atts['facility'] : ''),
    'membership_category' => isset($_GET['ccn_membership_category']) ? sanitize_text_field($_GET['ccn_membership_category']) : (isset($atts['membership_category']) ? $atts['membership_category'] : ''), // Corrected key
);

$clubs = $this_ref->get_clubs_data($filters);
$available_filters = $this_ref->get_available_filters(); // Fetches cities, facilities, membership_categories

// Pagination
$per_page = isset($atts['per_page']) ? intval($atts['per_page']) : 9; // Default to 9 for 3x3 grid
$current_page = get_query_var('paged') ? get_query_var('paged') : 1;
$total_clubs = count($clubs);
$total_pages = ceil($total_clubs / $per_page);

// Slice the clubs array for pagination
$offset = ($current_page - 1) * $per_page;
$clubs_paged = array_slice($clubs, $offset, $per_page);

// Base URL for pagination links (handles existing query parameters)
$base_url = remove_query_arg('paged', get_pagenum_link(999999999)); // Use WP function for base URL

// Determine initial view (passed from shortcode atts)
$initial_view = isset($atts['view']) ? $atts['view'] : 'grid';
?>

<div class="ccn-container" id="ccn-main-container" data-view="<?php echo esc_attr($initial_view); ?>">
    <div class="ccn-header">
        <h2 class="ccn-title">Explore Our <span class="ccn-title-highlight">Club Network</span></h2>
        <p class="ccn-subtitle">Find the perfect City Club location with our interactive tools</p>
    </div>

    <div class="ccn-view-toggle">
        <a href="#" class="ccn-view-button ccn-grid-view <?php echo ($initial_view === 'grid') ? 'active' : ''; ?>" data-view="grid">Grid View</a>
        <a href="#" class="ccn-view-button ccn-map-view <?php echo ($initial_view === 'map') ? 'active' : ''; ?>" data-view="map">Map View</a>
    </div>

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
                        <select id="ccn-membership-filter" name="ccn_membership_category"> <!-- Corrected name -->
                            <option value="">Membership</option>
                            <?php if (!empty($available_filters['membership_categories'])) : ?> <!-- Corrected key -->
                                <?php foreach ($available_filters['membership_categories'] as $membership) : ?> <!-- Corrected key -->
                                    <option value="<?php echo esc_attr($membership['slug']); ?>" <?php selected($filters['membership_category'], $membership['slug']); ?>> <!-- Corrected filter key -->
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
                if (!in_array($key, ['ccn_city', 'ccn_facility', 'ccn_membership_category', 'paged'])) { // Corrected key
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr(sanitize_text_field($value)) . '" />';
                }
            }
            ?>
        </form>
    </div>

    <!-- Grid View Content Area -->
    <div class="ccn-content-area ccn-grid-view-content <?php echo ($initial_view === 'grid') ? 'active' : ''; ?>" <?php echo ($initial_view !== 'grid') ? 'style="display: none;"' : ''; ?>>
        <?php if (!empty($clubs_paged)) : ?>
            <div class="ccn-clubs-grid">
                <?php foreach ($clubs_paged as $club) : ?>
                    <div class="ccn-club-card" data-club-id="<?php echo esc_attr($club['id']); ?>" data-latitude="<?php echo esc_attr($club['latitude']); ?>" data-longitude="<?php echo esc_attr($club['longitude']); ?>">
                        <div class="ccn-club-image">
                            <?php if (!empty($club['thumbnail'])) : ?>
                                <img src="<?php echo esc_url($club['thumbnail']); ?>" alt="<?php echo esc_attr($club['title']); ?>" loading="lazy">
                            <?php else : ?>
                                 <img src="<?php echo esc_url(CCN_PLUGIN_URL . 'public/images/default-club-image.jpg'); ?>" alt="<?php echo esc_attr($club['title']); ?>" loading="lazy">
                            <?php endif; ?>

                            <div class="ccn-club-image-overlay">
                                <h3 class="ccn-club-title">
                                    <a href="#" class="ccn-view-details-button" data-club-id="<?php echo esc_attr($club['id']); ?>"><?php echo esc_html($club['title']); ?></a>
                                </h3>
                                <?php if (!empty($club['address'])) : ?>
                                    <div class="ccn-club-address">
                                        <i class="ccn-location-icon"></i> <!-- Location icon via CSS -->
                                        <span><?php echo esc_html($club['address']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($club['is_premium'])) : ?>
                                <div class="ccn-premium-badge">PREMIUM</div>
                            <?php endif; ?>
                        </div>

                        <div class="ccn-club-content">
                            <?php if (!empty($club['facilities'])) : ?>
                                <div class="ccn-club-facilities">
                                    <?php foreach ($club['facilities'] as $facility) : ?>
                                        <div class="ccn-facility-item" title="<?php echo esc_attr($facility['description'] ?: $facility['name']); ?>">
                                            <span class="ccn-facility-icon">
                                                <?php
                                                if (!empty($facility['icon_url'])) {
                                                    echo '<i style="background-image: url(\'' . esc_url($facility['icon_url']) . '\');"></i>';
                                                } else {
                                                    $icon_slug = sanitize_title($facility['slug'] ?: 'default');
                                                    $icon_class = 'ccn-facility-icon-' . $icon_slug;
                                                    echo '<i class="' . esc_attr($icon_class) . '"></i>';
                                                }
                                                ?>
                                            </span>
                                            <?php echo esc_html($facility['name']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="ccn-club-info-bar">
                                <?php if (!empty($club['opening_hours'])) : ?>
                                    <div class="ccn-club-hours">
                                        <i class="ccn-clock-icon"></i>
                                        <span><?php echo esc_html($club['opening_hours']); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($club['rating']) && $club['rating'] > 0) : ?>
                                    <div class="ccn-club-rating">
                                        <span class="ccn-rating-stars" title="<?php echo esc_attr($club['rating']); ?> out of 5 stars">
                                            <?php
                                            $rating = floatval($club['rating']);
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($rating >= $i) {
                                                    echo '<i class="ccn-star-icon filled"></i>';
                                                } elseif ($rating > ($i - 1)) {
                                                    if ($rating - ($i-1) >= 0.75) { echo '<i class="ccn-star-icon filled"></i>'; }
                                                    elseif ($rating - ($i-1) >= 0.25) { echo '<i class="ccn-star-icon half-filled"></i>'; }
                                                    else { echo '<i class="ccn-star-icon empty"></i>'; }
                                                } else { echo '<i class="ccn-star-icon empty"></i>'; }
                                            }
                                            ?>
                                        </span>
                                        <?php if (!empty($club['reviews_count'])) : ?>
                                            <span class="ccn-reviews-count">(<?php echo esc_html($club['reviews_count']); ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="ccn-club-actions">
                                <a href="#" class="ccn-view-details-button" data-club-id="<?php echo esc_attr($club['id']); ?>">View Details</a>
                                <?php
                                $directions_url = '#';
                                if (!empty($club['latitude']) && !empty($club['longitude'])) {
                                    $directions_url = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($club['latitude'] . ',' . $club['longitude']);
                                } elseif (!empty($club['address'])) {
                                    $directions_url = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($club['address']);
                                }
                                ?>
                                <?php if ($directions_url !== '#') : ?>
                                <a href="<?php echo esc_url($directions_url); ?>" class="ccn-get-directions-button" target="_blank" rel="noopener noreferrer">Get Directions</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1) : ?>
                <div class="ccn-pagination">
                    <?php
                    echo paginate_links( array(
                        'base'      => $base_url . '%_%',
                        'format'    => (strpos($base_url, '?') ? '&' : '?') . 'paged=%#%',
                        'current'   => max( 1, $current_page ),
                        'total'     => $total_pages,
                        'prev_text' => __('<'),
                        'next_text' => __('>'),
                        'type'      => 'list',
                        'add_args'  => false,
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

    <!-- Map View Content Area (Initially empty or contains map partial if view=map) -->
    <div class="ccn-content-area ccn-map-view-content <?php echo ($initial_view === 'map') ? 'active' : ''; ?>" <?php echo ($initial_view !== 'map') ? 'style="display: none;"' : ''; ?>>
        <?php if ($initial_view !== 'map') : ?>
            <!-- Placeholder shown only when grid is the initial view -->
            <div class="ccn-map-loading-placeholder">
                <?php _e('Loading Map View...', 'city-club-network'); ?>
            </div>
        <?php endif; ?>
        <?php
            // If map is the initial view, the map partial is included by the shortcode handler directly.
            // If grid is initial, this container is empty and JS will load the content via AJAX.
        ?>
    </div>

</div>
