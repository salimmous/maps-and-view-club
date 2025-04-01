<?php
/**
 * Template for displaying the City Club Network in grid-only view with filters.
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
                        </div>

                        <div class="ccn-club-content">
                            <h3 class="ccn-club-title">
                                <?php echo esc_html($club['title']); ?>
                            </h3>
                            
                            <?php if (!empty($club['address'])) : ?>
                                <div class="ccn-club-address">
                                    <i class="ccn-location-icon"></i>
                                    <span><?php echo esc_html($club['address']); ?></span>
                                </div>
                            <?php endif; ?>

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
                                                    $icon_class = $icon_slug . '-icon';
                                                    echo '<i class="' . esc_attr($icon_class) . '"></i>';
                                                }
                                                ?>
                                            </span>
                                            <?php echo esc_html($facility['name']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

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

                            <div class="ccn-club-actions">
                                <?php if (!empty($club['permalink'])) : ?>
                                    <a href="<?php echo esc_url($club['permalink']); ?>" class="ccn-view-details-button">
                                        View Details
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($club['latitude']) && !empty($club['longitude'])) : ?>
                                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo esc_attr($club['latitude']); ?>,<?php echo esc_attr($club['longitude']); ?>" target="_blank" class="ccn-get-directions-button">
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
                    <ul>
                        <?php if ($current_page > 1) : ?>
                            <li>
                                <a href="<?php echo esc_url(add_query_arg('paged', $current_page - 1, $base_url)); ?>" class="prev">&laquo;</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <li>
                                <?php if ($i == $current_page) : ?>
                                    <span class="current"><?php echo esc_html($i); ?></span>
                                <?php else : ?>
                                    <a href="<?php echo esc_url(add_query_arg('paged', $i, $base_url)); ?>"><?php echo esc_html($i); ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages) : ?>
                            <li>
                                <a href="<?php echo esc_url(add_query_arg('paged', $current_page + 1, $base_url)); ?>" class="next">&raquo;</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="ccn-no-clubs-found">
                <p>No clubs found matching your criteria. Please try different filters.</p>
            </div>
        <?php endif; ?>
    </div>
</div>