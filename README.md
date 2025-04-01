# City Club Network WordPress Plugin

## Overview
City Club Network is a WordPress plugin designed to display fitness clubs across Morocco with interactive grid and map views. The plugin allows users to easily browse and filter club locations, making it simple for visitors to find the perfect fitness club based on their preferences.

## Features
- **Dual View Options**: Display clubs in either a grid layout or an interactive map view.
- **Advanced Filtering**: Filter clubs by city, facilities, and membership categories.
- **Responsive Design**: Fully responsive interface that works on all devices.
- **Detailed Club Modal**: AJAX-powered modal with tabs for Overview, Facilities, Classes (including PDF schedule download), and Membership plans.
- **Premium Club Highlighting**: Special indicators for premium club locations on both map and grid views.
- **Interactive Map**: Google Maps integration with custom markers, location details, and sidebar list interaction.
- **Admin Settings Page**: Configure Google Maps API Key, customize button text, and set primary/secondary/text colors.
- **Customizable Display**: Various shortcode parameters to customize the display.
- **Detailed Club Data**: Add comprehensive club details including address, multiple opening hours, rating, contact info, booking URLs, class lists, membership plans, and PDF schedules via WordPress admin.

## Installation

1. Download the plugin zip file.
2. Log in to your WordPress admin panel.
3. Navigate to **Plugins > Add New**.
4. Click on the **Upload Plugin** button at the top of the page.
5. Choose the downloaded zip file and click **Install Now**.
6. After installation, click **Activate Plugin**.

## Configuration

### Settings Page
Configure the plugin via the WordPress admin menu: **Settings > City Club Network**.

1.  **General Settings**:
    *   **Google Maps API Key**: Enter your Google Maps API Key. This is **required** for map functionality (both map view and location picker in admin).
        > **Note**: If you don't have a Google Maps API key, you can obtain one from the [Google Cloud Platform Console](https://console.cloud.google.com/). Make sure to enable the **Maps JavaScript API** and **Places API** for your key.

2.  **Appearance Customization**:
    *   **Primary Color**: Main accent color (e.g., buttons, links, active tabs).
    *   **Secondary Color**: Secondary color (e.g., backgrounds, highlights).
    *   **Default Text Color**: Default text color for descriptions and content.
    *   **Button Text Color**: Text color for primary buttons.
    *(Note: Applying colors might require theme CSS adjustments for full effect).*

3.  **Button Text Customization**:
    *   **"Book a Tour" Text**: Customize the text for the "Book a Tour" button in the modal.
    *   **"Get Directions" Text**: Customize the text for the "Get Directions" button on cards and in the modal.
    *   **"Choose Plan" Text**: Customize the text for the "Choose Plan" button on membership cards in the modal.

4.  Click **Save Settings** after making changes.

## Adding Club Locations

1.  In your WordPress admin, go to **City Clubs > Add New**.
2.  Enter the club name in the title field.
3.  Add a description in the main content editor (this appears in the modal's Overview tab).
4.  Set a **Featured Image** (Club Image) for the club (displayed in grid and map views).
5.  Fill in the **Club Details & Overview** meta box:
    *   **Club Address**: Full street address.
    *   **Premium Club**: Check if this is a premium location.
    *   **Opening Hours**: Enter hours for Monday-Friday, Saturday, and Sunday (e.g., "6:00 AM - 10:00 PM").
    *   **Rating (0-5)**: Enter the club's rating (e.g., 4.5).
    *   **Number of Reviews**: Enter the total number of reviews.
    *   **Contact Information**: Phone number, email address, website URL.
    *   **"Book a Tour" Button URL**: Enter the URL for the booking page (optional, enables button in modal).
6.  Fill in the **Club Location** meta box:
    *   Enter the **Latitude** and **Longitude** manually, OR
    *   Enter the address above and use the map (requires API key) to pinpoint the location (click/drag marker). Coordinates will be auto-filled.
7.  Fill in the **Club Classes & Schedule** meta box:
    *   **Classes Data**: Enter class details, one per line, separated by `|`: `ClassName|DaysAndTime|Level|InstructorName` (e.g., `Yoga Flow|Tue, Thu - 6:00 PM|all levels|Leila Mansouri`).
    *   **Schedule PDF URL**: Upload or select a PDF using the WordPress media library. This enables "View PDF" and "Download PDF" buttons in the modal's Classes tab.
8.  Fill in the **Club Membership Plans** meta box:
    *   **Membership Plans Data**: Enter plan details, one per line, separated by `|`: `PlanName|Price|Frequency|Features(comma,separated)|IsPopular(1 or 0)|ChoosePlanURL` (e.g., `Premium|499 MAD|per month|Unlimited access,Unlimited classes|1|https://example.com/premium`).
9.  Assign **Taxonomies** on the right sidebar:
    *   **Cities**: Select the city the club belongs to.
    *   **Facilities**: Check all applicable facilities (add new ones under City Clubs > Facilities). Add Icon URL and Description when creating/editing facilities for better display.
    *   **Membership Categories**: Select the types of memberships offered (add new ones under City Clubs > Membership Categories).
10. **Publish** the club.

## Using the Shortcode

To display the club network on any page or post, use the following shortcode:

```
[city_club_network]
```

### Shortcode Parameters

The shortcode accepts several parameters to customize the display:

| Parameter             | Description                                  | Default            | Options                   |
| --------------------- | -------------------------------------------- | ------------------ | ------------------------- |
| `view`                | Initial display mode                         | `grid`             | `grid`, `map`             |
| `city`                | Filter by city slug                          | empty (all cities) | city slug (e.g., `casablanca`) |
| `facility`            | Filter by facility slug                      | empty (all)        | facility slug (e.g., `pool`) |
| `membership_category` | Filter by membership category slug           | empty (all)        | category slug (e.g., `premium`) |
| `per_page`            | Number of clubs per page (Grid View only)    | `9`                | any positive number       |

### Examples

**Display clubs in map view initially:**
```
[city_club_network view="map"]
```

**Display clubs in Casablanca only:**
```
[city_club_network city="casablanca"]
```

**Display clubs with a swimming pool facility:**
```
[city_club_network facility="swimming-pool"]
```

**Display clubs offering premium membership category:**
```
[city_club_network membership_category="premium"]
```

**Display 12 clubs per page in grid view:**
```
[city_club_network per_page="12"]
```

## Customization

### CSS Customization
The plugin includes CSS files that can be overridden in your theme:

-   `public/css/city-club-network-public.css` - General styling for grid view, filters, etc.
-   `public/css/city-club-network-map.css` - Map view specific styling (sidebar, map container).
-   `public/css/city-club-network-modal.css` - Styling for the club details modal.

To customize the appearance, you can add custom CSS to your theme's `style.css` file or use a custom CSS plugin. You can also use the color settings on the plugin's settings page for basic color adjustments.

### Template Customization
Advanced users can copy the template files from the plugin's `public/partials` directory to a `city-club-network` folder within their active theme's directory (e.g., `wp-content/themes/your-theme/city-club-network/`). The plugin will automatically use the theme's templates if they exist, allowing for complete structural customization.

-   `city-club-network-grid-view.php`
-   `city-club-network-map-view.php`

## Troubleshooting

### Map Not Displaying or Location Picker Not Working
-   Ensure you've entered a valid **Google Maps API Key** in **Settings > City Club Network**.
-   Check that your API key has the **Maps JavaScript API** and **Places API** enabled in your Google Cloud Console.
-   Verify that the clubs have valid **Latitude** and **Longitude** coordinates saved in their post meta.
-   Check your browser's developer console for any JavaScript errors related to Google Maps.

### Filters Not Working
-   Make sure you've assigned the appropriate taxonomies (**Cities**, **Facilities**, **Membership Categories**) to your club posts.
-   Check that the slug names used in your shortcode parameters (e.g., `city="casablanca"`, `membership_category="premium"`) exactly match the actual taxonomy term slugs in WordPress.

### Modal Not Opening or Showing Errors
-   Check your browser's developer console for JavaScript errors.
-   Ensure the theme or another plugin isn't causing JavaScript conflicts.
-   Verify the AJAX endpoint (`admin-ajax.php`) is accessible.

## Support

For support requests, feature suggestions, or bug reports, please contact the plugin developer or repository maintainer.

---

*This plugin is designed to help fitness clubs in Morocco showcase their locations and facilities to potential members.*
