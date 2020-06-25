<?php
/**
 * Plugin Name:     North Asheville Tailgate Plugin
 * Plugin URI:      https://longlivesimple.com
 * Description:     Adds Business Listings custom post type and blocks for vendors, members, and sponsors listings. Adds user roles for Member, Sponsor, Vendor.
 * Author:          Long Live Simple
 * Author URI:      https://longlivesimple.com
 * Text Domain:     natm-plugin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Natm_Plugin
 */

/********************* Activate ***********************/

function natm_register_custom_post_type() {
    register_post_type('natm_listing',
        array(
            'labels'      => array(
                'name'          => __('Listings', 'textdomain'),
                'singular_name' => __('Listing', 'textdomain'),
                'menu_name' => __('Business Listings', 'textdomain'),
                'new_item' => __('Add New Listing', 'textdomain'),
                'add_new_item' => __('Add New Listing', 'textdomain'),
                'edit_item' => __('Edit Listing', 'textdomain'),
                'all_items' => __('All Listings', 'textdomain'),
                'featured_image' => __('Business Logo or Photo', 'textdomain'),
                'set_featured_image' => __('Add Business Logo or Photo', 'textdomain'),
            ),
                'public'      => true,
                'has_archive' => true,
                'show_in_rest' => true,
                'supports' => array('title', 'thumbnail', 'author', 'excerpt'),
                'template' => array(
                    array( 'core/paragraph', array(
                        'placeholder' => 'Business Description...',
                    ) ),
                ),
                'menu_icon' => 'dashicons-store',
                'rewrite' => array('slug' => 'listing'),
                'capability_type' => 'listing'
        )
    );
}

add_action('init', 'natm_register_custom_post_type');

add_filter('enter_title_here', function($input) {
    if('natm_listing' === get_post_type()) {
        return __('Business Name...', 'textdomain');
    } else {
        return $input;
    }
});

function add_wp_taxonomy_to_natm_listing() {
	register_taxonomy_for_object_type( 'category', 'natm_listing' );
}

add_action( 'init', 'add_wp_taxonomy_to_natm_listing' );

// Add custom roles for business users
function natm_add_custom_business_roles() {
    add_role('vendor', __('Vendor'), array(
        'read' => true, // Allows a user to read
        'read_listing' => true,
        'edit_listings' => true, // Allows user to edit their own listings
        'edit_published_listings' => true,
        'upload_files' => true
    ));
    add_role('member', __('Member'), array(
        'read' => true, // Allows a user to read
        'read_listing' => true,
        'edit_listings' => true, // Allows user to edit their own listings
        'edit_published_listings' => true,
        'upload_files' => true
    ));
    add_role('sponsor', __('Sponsor'), array(
        'read' => true, // Allows a user to read
        'read_listing' => true,
        'edit_listings' => true, // Allows user to edit their own listings
        'edit_published_listings' => true,
        'upload_files' => true
    ));

    $admin_role = get_role('administrator');
    $admin_role->add_cap('edit_listing');
    $admin_role->add_cap('read_listing');
    $admin_role->add_cap('delete_listing');
    $admin_role->add_cap('edit_listings');
    $admin_role->add_cap('edit_others_listings');
    $admin_role->add_cap('publish_listings');
    $admin_role->add_cap('read_private_listings');
    $admin_role->add_cap('delete_listings');
    $admin_role->add_cap('delete_private_listings');
    $admin_role->add_cap('delete_published_listings');
    $admin_role->add_cap('delete_others_listings');
    $admin_role->add_cap('edit_private_listings');
    $admin_role->add_cap('edit_published_listings');
}

// Custom blocks
function natm_listings_grid_block_render_callback($attributes) {
    $recent_posts = get_posts( array(
        'numberposts' => -1,
        'post_status' => 'publish',
        'post_type' => 'natm_listing',
        'orderby' => 'title',
        'order' => 'ASC',
        'category' => $attributes['selectedCategory']
    ) );
    if ( count( $recent_posts ) === 0 ) {
        return 'No listings';
    }
    $rendered_posts = '';
    foreach($recent_posts as $post) {
        $business_name = esc_html( get_the_title( $post->ID ) );
        $business_desc = wp_trim_words(get_field('business_description', $post->ID), 30);
        $business_address = get_field('business_address', $post->ID);
        $post_permalink = get_permalink($post->ID);
        $business_url = esc_url( get_field('business_website', $post->ID) );
        $business_website = '';
        if ($business_url) {
            $business_website = '<a class="has-small-font-size" href="' . $business_url . '">Visit ' . $business_name .  '\'s website</a>';
        }

        $rendered_posts .= sprintf(
            '<li class="natm-business-listing--grid-item">
                <p class="listing-thumbnail">
                    %1$s
                </p>
                <h4 class="listing-headline">
                    %2$s
                </h4>
                <p class="listing-address">
                    %3$s
                </p>
                <p class="listing-description">
                    %4$s <a class="has-small-font-size" href="%5$s">Learn More</a>
                </p>
                <p class="listing-website">
                    %6$s
                </p>
            </li>',
            get_the_post_thumbnail($post->ID),
            $business_name,
            $business_address,
            $business_desc,
            $post_permalink,
            $business_website
        );
    }
    return '<ul class="natm-business-listing--grid my-l">' . $rendered_posts . '</ul>';
}

function natm_load_block_listings_grid() {
    $asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');

    wp_register_script(
        'listings-grid',
        plugins_url( 'build/index.js', __FILE__ ),
        $asset_file['dependencies'],
        $asset_file['version']
    );

    register_block_type( 'natm/listings-grid', array(
        'editor_script' => 'listings-grid',
        // 'attributes' => array(
        //     'selectedCategory' => array(
        //         'type' => 'number',
        //         'default' => null
        //     )
        // ),
        'render_callback' => 'natm_listings_grid_block_render_callback'
    ) );
}
add_action('init', 'natm_load_block_listings_grid');

// Plugin Activation
function natm_plugin_activate() {
    natm_register_custom_post_type();
    natm_add_custom_business_roles();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'natm_plugin_activate');


/********************* Deactivate ***********************/

// Unregister post types
function natm_unregister_custom_post_type() {
    unregister_post_type('natm_listing');
}

// Remove new role
function natm_remove_business_roles() {
    remove_role('vendor');
    remove_role('member');
    remove_role('sponsor');
}

// Plugin Deactivation
function natm_plugin_deactivate() {
    natm_unregister_custom_post_type();
    natm_remove_business_roles();
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'natm_plugin_deactivate');
