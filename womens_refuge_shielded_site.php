<?php

/**
 * Women's Refuge Shielded Site
 *
 * @link              https://github.com/PhilTanner/womens_refuge_shielded_site
 * @since             0.0.1
 * @package           WRSS
 * @copyright         2021 Phil Tanner
 *
 * @wordpress-plugin
 * Plugin Name:       Women's Refuge Shielded Site
 * Plugin URI:        https://github.com/PhilTanner/womens_refuge_shielded_site
 * Description:       Add the NZ Women's Refuge Shielded Site button to your site using the [womens_refuge_shield] shortcode or widget.
 * Version:           1.0.1
 * Author:            Phil Tanner
 * Author URI:        https://twitter.com/Phil_Tanner
 * License:           Apache-2.0
 * License URI:       https://www.apache.org/licenses/LICENSE-2.0.html
 * Text Domain:       WRSS
 * Domain Path:       /languages
 *
 *
 *    Copyright 2021 Phil Tanner
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
$plugin_data = get_file_data(__FILE__, array('Version' => 'Version') );
define( 'WRSS_VERSION',     $plugin_data['Version'] );

/**
 * Couple of quick-access values to working out our root dir, both file system & URL access.
 *
 * @since    0.0.1
 */
define( 'WRSS_PATH',   __DIR__.DIRECTORY_SEPARATOR );
define( 'WRSS_URL',    plugins_url( '/', __FILE__ ) );

/**
 * Allow not just a shortcode, but a widget to be used.
 *
 * @since    0.0.2
 */
require_once( 'includes'.DIRECTORY_SEPARATOR.'class-wrss_widget.php' );

/**
 * Setup our default arguments so we can refer to them from all over the place.
 *
 * @since    0.0.2
 */
function WRSS_default_args(){
	return array(
		'icon_size'  => 'large', // Accepts 'large','small','custom'
		'modal_id'   => 'modal',
		'element_id' => 'shielded-logo',
	);
}

/**
 * Handle our shortcode requests
 *
 * @since    0.0.1
 */
function WRSS_shield( $atts = array(), $content = null, $tag = '' ){

	// Default our arguments
	$atts = shortcode_atts(
		WRSS_default_args(),
		$atts,
		$tag
	);

	// We're only going to accept 'large'/'small'/'custom'
	switch( $atts['icon_size'] ){
		case 'large':
			$w = '60';
			$h = '79.5';
			break;
		case 'small':
			$w = '60';
			$h = '61';
			break;
		// We're going to fall back to this one
		default:
			$atts['icon_size'] = 'custom';
			$w = '60';
			$h = '60';
	}

	// Generate our HTML
	$output = '<div class="wrss_container">'."\n";
	$output .= '	<img alt="'.esc_attr( __('Women\'s Refuge Shielded Site', 'WRSS' ) ).'" '."\n";
	$output .= '		id="'.esc_attr($atts['element_id']).'" '."\n";
	$output .= '		src="'.WRSS_URL.'/img/'.$atts['icon_size'].'-logo.png" '."\n";
	$output .= '		height="'.$h.'" '."\n";
	$output .= '		width="'.$w.'" '."\n";
	$output .= '		style="cursor: pointer; margin: 0px auto; display: inherit;" />'."\n";
	//$output .= '	<script defer="defer" src="https://staticcdn.co.nz/embed/embed.js"></script>'."\n";

	$output .= '	<script>'."\n";
	$output .= '		(function () {'."\n";
	$output .= '			window.onload = function(){'."\n";
	$output .= '				var frameName_'.str_replace('-','_',esc_attr($atts['element_id'])).' = new ds07o6pcmkorn({'."\n";
	$output .= '					openElementId: "#'.esc_attr($atts['element_id']).'",'."\n";
	$output .= '					modalID:       "'.esc_attr($atts['modal_id']).'",'."\n";
	$output .= '				});'."\n";
	$output .= '				frameName_'.str_replace('-','_',esc_attr($atts['element_id'])).'.init();'."\n";
	$output .= '			}'."\n";
	$output .= '		})();'."\n";
	$output .= '	</script>'."\n";


	$output .= '</div>'."\n";

	// Pass it back for inclusion
	return $output;
}
add_shortcode( 'womens_refuge_shield', 'WRSS_shield' );


/**
 * WordPress approval process wanted the script moved to enqueue - so we're including
 * here to meet those requirements - it means we're ALWAYS including it, even if
 * the icon isn't used - we'll just have to assume that if they've got the plugin,
 * and it's activated, they are using it somewhere.
 *
 * @since    1.0.1
 */
function wrss_embed_external_script() {
	//wp_register_script( 'wrss_external_embed', );
	wp_enqueue_script( 'wrss_external_embed', 'https://staticcdn.co.nz/embed/embed.js'  );
}
add_action( 'wp_enqueue_scripts', 'wrss_embed_external_script' );

/**
 * Add some additional links underneath our plugin description,
 * linking back to the orginal websites for more information
 *
 * @since    1.0.1
 */
function wrss_defer_embed_script($tag, $handle) {
	// We want to defer our embed, as it's unlikely the user will ever click the
	// button before the page has loaded, and there's no point slowing the rendering
	// for it.
	if( $handle == 'wrss_external_embed' ){
		return str_ireplace(' src', ' defer src', $tag);
	}
	// Not our tag, so we'll return it as we found it.
	return $tag;
}
add_filter('script_loader_tag', 'wrss_defer_embed_script', 10, 2);

/**
 * Add some additional links underneath our plugin description,
 * linking back to the orginal websites for more information
 *
 * @since    0.0.1
 */
function wrss_plugin_links( $links_array, $plugin_file_name, $plugin_data, $status ){
	if( strpos( $plugin_file_name, basename(__FILE__) ) ) {
		$links_array[] = '<a href="https://shielded.co.nz/" target="_blank" class="dashicons-before dashicons-external">'.__('The Shielded Site Project','WRSS').'</a>';
		$links_array[] = '<a href="https://womensrefuge.org.nz/" target="_blank" class="dashicons-before dashicons-external">'.__('Women\'s Refuge NZ', 'WRSS').'</a>';
	}
	return $links_array;
}
add_filter( 'plugin_row_meta', 'wrss_plugin_links', 10, 4 );

/**
 * Add a Donation link to the plugin deactivate area.
 * Donating to the Refuge, not me.
 *
 * @since    0.0.1
 */
function wrss_action_links( $links_array, $plugin_file_name, $plugin_data, $status ){
	if( strpos( $plugin_file_name, basename(__FILE__) ) ) {
		$links_array[] = '<a href="https://womensrefuge.org.nz/make-a-donation/" class="dashicons-before dashicons-money-alt">'.__('Donate to Women\'s Refuge', 'WRSS').'</a>';
	}
	return $links_array;
}
add_filter( 'plugin_action_links', 'wrss_action_links', 10, 4 );
