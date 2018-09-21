<?php
/**
 * Plugin Name: qTranslate-XT
 * Plugin URI: http://github.com/qtranslate/qtranslate-xt/
 * Description: Adds user-friendly and database-friendly multilingual content support.
 * Version: 3.5.1
 * Author: qTranslate Community
 * Author URI: http://github.com/qtranslate/
 * Tags: multilingual, multi, language, admin, tinymce, Polyglot, bilingual, widget, switcher, professional, human, translation, service, qTranslate, zTranslate, mqTranslate, qTranslate Plus, WPML
 * Text Domain: qtranslate
 * Domain Path: /lang/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Author e-mail: herrvigg@gmail.com
 * Original Author: John Clause and Qian Qin (http://www.qianqin.de mail@qianqin.de)
 * GitHub Plugin URI: https://github.com/qtranslate/qtranslate-xt/
 */
/* Unused keywords (as described in http://codex.wordpress.org/Writing_a_Plugin):
 * Network: Optional. Whether the plugin can only be activated network wide. Example: true
 */
/*
	Copyright 2018  qTranslate Community

	The statement below within this comment block is relevant to
	this file as well as to all files in this folder and to all files
	in all sub-folders of this folder recursively.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
/*
 * Search for 'Designed as interface for other plugin integration' in comments to functions
 * to find out which functions are safe to use in the 3rd-party integration.
 * Avoid accessing internal variables directly, as they are subject to be re-designed at any time.
*/
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
/**
 * The constants defined below are
 * Designed as interface for other plugin integration. The documentation is available at
 * https://qtranslatexteam.wordpress.com/integration/
 */
define( 'QTX_VERSION', '3.5.1' );

if ( ! defined( 'QTRANSLATE_FILE' ) ) {
	define( 'QTRANSLATE_FILE', __FILE__ );
	define( 'QTRANSLATE_DIR', dirname( __FILE__ ) );
}

require_once( QTRANSLATE_DIR . '/inc/qtx_class_translator.php' );

if ( is_admin() ) { // && !(defined('DOING_AJAX') && DOING_AJAX) //todo cleanup
	require_once( QTRANSLATE_DIR . '/admin/qtx_activation_hook.php' );
	qtranxf_register_activation_hooks();
}

// load additional functionalities
/*
class KM_REST_Pages_Controller extends WP_REST_Posts_Controller {
}
/**
 * Set up a custom REST API controller class for the Page post type.
 *
 * @author Kellen Mace
 *
 * @param  array  $args The post type arguments.
 * @param  string $name The name of the post type.
 *
 * @return array $args The post type arguments, possibly modified.
 */
function qtranxf_register_rest_controller( $args, $name ) {
	// Tell WordPress to use our KM_REST_Pages_Controller class
	// for Page REST API requests.
	$args['rest_controller_class'] = 'QTX_REST_Post_Controller';
	return $args;
}
//add_filter( 'register_post_type_args', 'qtranxf_register_rest_controller', 10, 2 );

function qtranxf_rest_prepare( $response, $post, $request ) {
	if ( 'edit' !== $request['context'] ) {
		return $response;
	}

	$response_data = $response->get_data();
	if ( isset( $response_data['content'] ) && is_array( $response_data['content'] ) && isset( $response_data['content']['raw'] ) ) {
		$lang = 'fr';
		$response_data['title']['raw'] = qtranxf_use($lang, $response_data['title']['raw']);
		$response_data['content']['raw'] = qtranxf_use($lang, $response_data['content']['raw']);
		$response_data['qtx'] = 'wtf';
		$response->set_data( $response_data );
	}

	return $response;
}
$post_type = 'post';
add_filter( "rest_prepare_{$post_type}", 'qtranxf_rest_prepare', 99, 3 );

function qtranxf_rest_request_before_callbacks( $response, $handler, $request ) {
	if ( $request->get_method() !== 'PUT' ) {
		return $response;
	}

	$post = get_post($request->get_param('id'), ARRAY_A);

	$request_body = json_decode($request->get_body(), true);
	$fields = [ 'content', 'title' ];
	foreach ( $fields as $field )
	if ( isset( $request_body[ $field ] ) ) {
		$edit_lang = 'fr';

		$new_value = $request_body[ $field ];

		$original_value = $post[ 'post_' . $field ];
		$blocks = qtranxf_get_language_blocks($original_value);
		if(count($blocks) <= 1)
			return FALSE;// no languages set
		$result = array();
		$content = qtranxf_split_languages($blocks);
		foreach($content as $language => $lang_text) {
			$lang_text = trim($lang_text);
			if(!empty($lang_text)) $result[] = $language;
		}

		$result[ $edit_lang ] = $new_value;

		$sep = '[';
		$new_data = qtranxf_collect_translations_deep( $result, $sep );

		$request_body[ $field ] =  $new_data;

		//$response_data['title']['raw'] = qtranxf_use($lang, $response_data['title']['raw']);
		//$response_data['content']['raw'] = qtranxf_use($lang, $response_data['content']['raw']);
		//$response->set_data( $response_data );
	}
	$request->set_body( json_encode($request_body) );

	return $response;
}
add_filter( 'rest_request_before_callbacks', 'qtranxf_rest_request_before_callbacks', 99, 3 );

add_action('init', function () {
	register_meta('post', 'qtx_admin',
		[
			'show_in_rest' => true,
			'single' => true
		]);
});
/*function qtranxf_insert_post_data( $data, $postarr ) {
	$data['post_title'] = '[:en]new title[:fr]nouveau titre[:it]nuovo titolo[:]';
	$data['post_content'] = '[:en]new content[:fr]nouveau contenu[:]';
	return $data;
}*/
//add_filter( 'wp_insert_post_data', 'qtranxf_insert_post_data', 99, 2 );

function qtranxf_register_blocks() {
	$ver = filemtime( plugin_dir_path( __FILE__ ). 'admin/js/block.js');

	wp_register_script(
		'qtx-gutenberg',
		plugins_url( 'admin/js/block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element' ),
		$ver,
		true
	);

	register_block_type( 'qtx/qtx-admin', array(
		'editor_script' => 'qtx-gutenberg',
	) );
}

add_action( 'init', 'qtranxf_register_blocks' );

//if(file_exists(QTRANSLATE_DIR.'/dev/slugs'))
//	require_once(QTRANSLATE_DIR.'/dev/slugs/qtx_slug.php');
