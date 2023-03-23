# Assessment Plugin
The Assessment Plugin is a WordPress plugin that allows you to register a custom post type unit and consume a JSON API to create unit records in your WordPress site. The plugin also provides an admin page where you can trigger the API call and a shortcode to display a list of the unit posts on the front-end of your site.

## Installation
To install the plugin, follow these steps:

* Download the latest version of the plugin from the GitHub repository.
* Extract the plugin files to the wp-content/plugins directory of your WordPress installation.
* Activate the plugin through the WordPress Plugins screen.

## Usage

### Custom Post Type
The plugin registers a custom post type called unit. Each unit post has custom fields for asset_id, building_id, floor_id, floor_plan_id, and area.

### Admin Page
The plugin provides an admin page in the WordPress CMS. The admin page includes a custom button to trigger an API call. When the button is clicked, the plugin consumes the JSON API and creates unit posts from the API data.

### Creating Unit Posts
To create unit posts, simply trigger the API call from the Assessment Plugin's admin page. The plugin will consume the JSON API and create unit records in your WordPress site, populating the required custom fields for each record. Alternatively, you can add new Units from the Units menu in your WordPress dashboard.

### Shortcode
The plugin provides a shortcode to display a list of the unit posts on the front end of the site. To use the shortcode, add the following code to any page or post:

```php
[assessment_plugin_unit_list]
```
The shortcode displays a reasonably styled list of the unit posts, broken into two sections: units with an area greater than 1 and units with an area of 1.

## API Information
The plugin consumes the following JSON API endpoint:

```php
https://api.sightmap.com/v1/assets/1273/multifamily/units?per-page=250
```
The API requires an API key as a header (API-Key: 7d64ca3869544c469c3e7a586921ba37). The plugin consumes the first 250 units from the API.

## Credits
Assessment Plugin was developed by **Ashime Amandong** as the technical assessment for a **_Backend Developer Role with Engrain_**.

If you have any questions or feedback, please contact me at [aashime.amandong@gmail.com].
