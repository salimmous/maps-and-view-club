<?php
/**
 * Template for displaying the City Club Network Search Bar.
 *
 * @since      1.4.0
 * @package    City_Club_Network
 */

// Ensure necessary variables are available
if (!isset($facilities) || !isset($form_action_url)) {
    echo '<p>Error: Required data not available for search bar.</p>';
    return;
}
?>

<div class="ccn-search-bar-container">
    <form class="ccn-search-bar-form" action="<?php echo esc_url($form_action_url); ?>" method="GET">
        <div class="ccn-search-input-wrapper">
            <i class="ccn-search-icon"></i>
            <input type="text" name="ccn_search_term" placeholder="<?php esc_attr_e('Enter city or club name', 'city-club-network'); ?>" class="ccn-search-input">
        </div>
        <div class="ccn-search-select-wrapper">
            <select name="ccn_facility" class="ccn-facility-select">
                <option value=""><?php esc_html_e('Facilities', 'city-club-network'); ?></option>
                <?php if (!empty($facilities)) : ?>
                    <?php foreach ($facilities as $facility) : ?>
                        <option value="<?php echo esc_attr($facility['slug']); ?>">
                            <?php echo esc_html($facility['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <button type="submit" class="ccn-search-submit-button">
            <?php esc_html_e('Find Clubs', 'city-club-network'); ?>
        </button>
         <?php
            // Add hidden fields to preserve existing query parameters if the form submits to the current page
            if (empty($atts['results_page_url'])) {
                foreach ($_GET as $key => $value) {
                    // Preserve all GET params except the ones used by this form
                    if (!in_array($key, ['ccn_search_term', 'ccn_facility'])) {
                        echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr(sanitize_text_field($value)) . '" />';
                    }
                }
            }
        ?>
    </form>
</div>
