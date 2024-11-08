<?php
/**
 * Plugin Name:  IONOS Journey
 * Plugin URI:   https://www.ionos.com
 * Description:  IONOS Journey is your guide through WordPress. If you have never used WordPress before, Journey will show you the essential parts of the interface. You can stop and restart your Journey at any time.
 * Version:      1.0.2
 * License:      GPLv2 or later
 * Author:       IONOS
 * Author URI:   https://www.ionos.com
 * Text Domain:  ionos-journey
*/
namespace Ionos\Journey;

/**
 * Init plugin.
 *
 * @return void
 */
function init() {
    require_once 'inc/lib/markdown/Markdown.inc.php';

    require_once 'inc/class-helper.php';
    require_once 'inc/class-settings.php';
    require_once 'inc/class-manager.php';
    require_once 'inc/class-profile.php';

    require_once 'inc/lib/options.php';
    Options::set_tenant_and_plugin_name('ionos', 'journey');

    require_once 'inc/lib/data-providers/cloud.php';
    require_once 'inc/lib/config.php';
    require_once 'inc/lib/updater.php';

    new Manager();
	new Profile();
}

\add_action( 'plugins_loaded', 'Ionos\Journey\init' );

/**
 * Plugin translation.
 *
 * @return void
 */
function load_textdomain() {
    if ( false !== \strpos( \plugin_dir_path( __FILE__ ), 'mu-plugins' ) ) {
        \load_muplugin_textdomain(
            'ionos-journey',
            \basename( \dirname( __FILE__ ) ) . '/languages'
        );
    } else {
        \load_plugin_textdomain(
            'ionos-journey',
            false,
            \dirname( \plugin_basename( __FILE__ ) ) . '/languages/'
        );
    }
}
\add_action( 'init', 'Ionos\Journey\load_textdomain' );

function disable_gutenberg_welcome_guide() {
    wp_add_inline_script( 'wp-data', "window.onload = function() {
        const isWelcomeGuidePost = wp.data.select( 'core/edit-post' ).isFeatureActive( 'welcomeGuide' );
        const customizeWidgetsSelect = wp.data.select( 'core/edit-widgets' );
        let isWelcomeGuideCustomizer;
        
        if ( customizeWidgetsSelect ) {
            isWelcomeGuideCustomizer = customizeWidgetsSelect.__unstableIsFeatureActive( 'welcomeGuide' );
            
            if ( isWelcomeGuideCustomizer ) {
                wp.data.dispatch( 'core/edit-widgets' ).__unstableToggleFeature( 'welcomeGuide' );
            }
        }
        
        if ( isWelcomeGuidePost ) {
            wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'welcomeGuide' );
        }
    }" );
}

add_action( 'enqueue_block_editor_assets', 'Ionos\Journey\disable_gutenberg_welcome_guide', 20 );