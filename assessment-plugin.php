<?php
/**
 * Plugin Name: Assessment Plugin
 * Plugin URI: #
 * Description: An assessment plugin for the role of a Backend dev with Engrain.
 * Version: 1.0.0
 * Author: Ashime Amandong
 * Author URI: mailto:aashime.amandong@gmail.com
 */

// Register the custom post type
function assessment_plugin_register_unit_post_type() {

    $labels = array(
        'name' => __( 'Units', 'assessment-plugin' ),
        'singular_name' => __( 'Unit', 'assessment-plugin' ),
        'menu_name' => __( 'Units', 'assessment-plugin' ),
        'add_new' => __( 'Add New', 'assessment-plugin' ),
        'add_new_item' => __( 'Add New Unit', 'assessment-plugin' ),
        'edit_item' => __( 'Edit Unit', 'assessment-plugin' ),
        'new_item' => __( 'New Unit', 'assessment-plugin' ),
        'view_item' => __( 'View Unit', 'assessment-plugin' ),
        'search_items' => __( 'Search Units', 'assessment-plugin' ),
        'not_found' => __( 'No Units found', 'assessment-plugin' ),
        'not_found_in_trash' => __( 'No Units found in Trash', 'assessment-plugin' )
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array( 'slug' => 'unit' ),
        'query_var' => true,
        'menu_icon' => 'dashicons-building',
        'supports' => array( 'title', 'custom-fields' ),
    );

    register_post_type( 'unit', $args );
}
add_action( 'init', 'assessment_plugin_register_unit_post_type' );


// Define the admin page
function assessment_plugin_admin_page() {
    
    // Check if the button was clicked
    if (isset($_POST['assessment_plugin_import_units'])) {
       
        // Set API Credentials
        $api_url = 'https://api.sightmap.com/v1/assets/1273/multifamily/units?per-page=250';
        $api_key = '7d64ca3869544c469c3e7a586921ba37';
    
        $args = array(
            'headers' => array(
                'API-Key' => $api_key,
            ),
        );
    
        $response = wp_remote_get( $api_url, $args );
    
        if ( is_wp_error( $response ) ) {
            return;
        }
    
        //Retrieve and Decode Json Response from API
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );
    
        if ( ! isset( $data->data ) || ! is_array( $data->data ) ) {
            return;
        }
    
        $units = $data->data;
        
        //Create Units from API response
        foreach ( $units as $unit ) {
            $unit_data = array(
                'post_title' => $unit->unit_number,
                'post_type' => 'unit',
                'post_status' => 'publish',
            );
    
            $post_id = wp_insert_post( $unit_data );
    
            if ( $post_id ) {
                update_post_meta( $post_id, 'asset_id', $unit->asset_id );
                update_post_meta( $post_id, 'building_id', $unit->building_id );
                update_post_meta( $post_id, 'floor_id', $unit->floor_id );
                update_post_meta( $post_id, 'floor_plan_id', $unit->floor_plan_id );
                update_post_meta( $post_id, 'area', $unit->area );
            }
        }
    
            // Redirect back to units post type admin page
            wp_redirect( admin_url( 'edit.php?post_type=unit&statusImport=success' ) );
            
        }
    // Display the admin page
    ?>
    <div class="wrap">
        <h1>Asssessment Plugin by Ashime</h1>
        <br>
        <h3>Import Units</h3>
        <p><em>Click the button below to import units from the API.</em></p>
        <form method="post">
            <input type="submit" name="assessment_plugin_import_units" class="button button-primary" value="Import Units">
        </form>
    </div>
    <?php
}

// The admin page
function assessment_plugin_add_admin_page() {
    add_menu_page(
        __( 'Assessment Plugin', 'assessment-plugin' ),
        __( 'Assessment Plugin', 'assessment-plugin' ),
        'manage_options', // Capability required to access the page
        'assessment-plugin', // Menu slug
        'assessment_plugin_admin_page', // Function to display the page
        'dashicons-admin-generic',
        30
    );
}
add_action( 'admin_menu', 'assessment_plugin_add_admin_page' );

// Add a custom column to the unit post list
function assessment_plugin_add_custom_columns( $columns ) {
    $columns['floor_plan_id'] = __( 'Floor Plan ID', 'assessment-plugin' );
    return $columns;
}
add_filter( 'manage_unit_posts_columns', 'assessment_plugin_add_custom_columns' );

// Populate the custom column with the floor_plan_id field
function assessment_plugin_populate_custom_columns( $column, $post_id ) {
    switch ( $column ) {
        case 'floor_plan_id':
            echo get_post_meta( $post_id, 'floor_plan_id', true );
            break;
    }
}
add_action( 'manage_unit_posts_custom_column', 'assessment_plugin_populate_custom_columns', 10, 2 );

// Add custom fields to the unit post edit page
function assessment_plugin_add_custom_fields() {
    add_meta_box(
        'assessment_plugin_custom_fields',
        __( 'Unit Information', 'assessment-plugin' ),
        'assessment_plugin_render_custom_fields',
        'unit',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'assessment_plugin_add_custom_fields' );

// Render the custom fields
function assessment_plugin_render_custom_fields( $post ) {
    // Retrieve the values of the custom fields
    $asset_id = get_post_meta( $post->ID, 'asset_id', true );
    $building_id = get_post_meta( $post->ID, 'building_id', true );
    $floor_id = get_post_meta( $post->ID, 'floor_id', true );
    $floor_plan_id = get_post_meta( $post->ID, 'floor_plan_id', true );
    $area = get_post_meta( $post->ID, 'area', true );
    // Output the HTML for the custom fields
?>
    <table class="form-table">
        <tr>
            <th><label for="assessment_plugin_asset_id"><?php _e( 'Asset ID', 'assessment-plugin' ); ?></label></th>
            <td><input type="text" name="assessment_plugin_asset_id" id="assessment_plugin_asset_id" value="<?php echo esc_attr( $asset_id ); ?>"></td>
        </tr>
        <tr>
            <th><label for="assessment_plugin_building_id"><?php _e( 'Building ID', 'assessment-plugin' ); ?></label></th>
            <td><input type="text" name="assessment_plugin_building_id" id="assessment_plugin_building_id" value="<?php echo esc_attr( $building_id ); ?>"></td>
        </tr>
        <tr>
            <th><label for="assessment_plugin_floor_id"><?php _e( 'Floor ID', 'assessment-plugin' ); ?></label></th>
            <td><input type="text" name="assessment_plugin_floor_id" id="assessment_plugin_floor_id" value="<?php echo esc_attr( $floor_id ); ?>"></td>
        </tr>
        <tr>
            <th><label for="assessment_plugin_floor_plan_id"><?php _e( 'Floor Plan ID', 'assessment-plugin' ); ?></label></th>
            <td><input type="text" name="assessment_plugin_floor_plan_id" id="assessment_plugin_floor_plan_id" value="<?php echo esc_attr( $floor_plan_id ); ?>"></td>
        </tr>
        <tr>
            <th><label for="assessment_plugin_area"><?php _e( 'Area', 'assessment-plugin' ); ?></label></th>
            <td><input type="text" name="assessment_plugin_area" id="assessment_plugin_area" value="<?php echo esc_attr( $area ); ?>"></td>
        </tr>
    </table>
    <?php
}

// Save the custom field values when the unit post is saved
function assessment_plugin_save_custom_fields( $post_id ) {
    update_post_meta( $post_id, 'asset_id', sanitize_text_field( $_POST['assessment_plugin_asset_id'] ) );
    update_post_meta( $post_id, 'building_id', sanitize_text_field( $_POST['assessment_plugin_building_id'] ) );
    update_post_meta( $post_id, 'floor_id', sanitize_text_field( $_POST['assessment_plugin_floor_id'] ) );
    update_post_meta( $post_id, 'floor_plan_id', sanitize_text_field( $_POST['assessment_plugin_floor_plan_id'] ) );
    update_post_meta( $post_id, 'area', sanitize_text_field( $_POST['assessment_plugin_area'] ) );
}
add_action( 'save_post_unit', 'assessment_plugin_save_custom_fields' );



// Register the shortcode
add_shortcode( 'assessment_plugin_unit_list', 'assessment_plugin_display_unit_list' );

// Define the shortcode function
function assessment_plugin_display_unit_list() {
    $output = '';

    // Get units with area > 1
    $query_args = array(
        'post_type' => 'unit',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'area',
                'value' => 1,
                'compare' => '>',
                'type' => 'NUMERIC',
            ),
        ),
    );

    $units_query = new WP_Query( $query_args );

    if ( $units_query->have_posts() ) {
        $output .= '<h2>Units with area greater than 1</h2>';
        $output .= '<ul>';

        while ( $units_query->have_posts() ) {
            $units_query->the_post();
            $unit_number = get_the_title();
            $unit_area = get_post_meta( get_the_ID(), 'area', true );

            $output .= '<li>' . $unit_number . ' - ' . $unit_area . ' sq ft</li>';
        }

        $output .= '</ul>';
    }

    wp_reset_postdata();

    // Get units with area = 1
    $query_args = array(
        'post_type' => 'unit',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'area',
                'value' => 1,
                'compare' => '=',
                'type' => 'NUMERIC',
            ),
        ),
    );

    $units_query = new WP_Query( $query_args );

    if ( $units_query->have_posts() ) {
        $output .= '<h2>Units with area equal to 1</h2>';
        $output .= '<ul>';

        while ( $units_query->have_posts() ) {
            $units_query->the_post();
            $unit_number = get_the_title();
            $unit_area = get_post_meta( get_the_ID(), 'area', true );

            $output .= '<li>' . $unit_number . ' - ' . $unit_area . ' sq ft</li>';
        }

        $output .= '</ul>';
    }

    wp_reset_postdata();

    return $output;
}



