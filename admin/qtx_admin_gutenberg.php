<?php
/**
 * Admin handler for Gutenberg
 * @author: herrvigg
 */

if (!defined('ABSPATH')) {
    exit;
}

// *//**
// * Set up a custom REST API controller class for the Page post type.
// *
// * @param  array  $args The post type arguments.
// * @param  string $name The name of the post type.
// *
// * @return array $args The post type arguments, possibly modified.
// */
//function qtranxf_register_rest_controller( $args, $name ) {
//	// Tell WordPress to use our KM_REST_Pages_Controller class
//	// for Page REST API requests.
//	$args['rest_controller_class'] = 'QTX_REST_Post_Controller';
//	return $args;
//}
//add_filter( 'register_post_type_args', 'qtranxf_register_rest_controller', 10, 2 );

function qtranxf_rest_prepare($response, $post, $request)
{
    global $q_config;

    if ($request['context'] !== 'edit' || $request->get_method() !== 'GET') {
        return $response;
    }

    assert(!$q_config['url_info']['doing_front_end']);
    // TODO allow user to select editor lang with buttons
    $editor_lang = isset($_GET['qtx_lang']) ? $_GET['qtx_lang'] : null;
    if (!isset($editor_lang) || !in_array($editor_lang, $q_config['enabled_languages'])) {
        $editor_lang = $q_config['url_info']['lang_admin'];
    }

    $response_data = $response->get_data();
    if (isset($response_data['content']) && is_array($response_data['content']) && isset($response_data['content']['raw'])) {
        $response_data['title']['raw'] = qtranxf_use($editor_lang, $response_data['title']['raw']);
        $response_data['content']['raw'] = qtranxf_use($editor_lang, $response_data['content']['raw']);
        $response_data['qtx_editor_lang'] = $editor_lang;
        $response->set_data($response_data);
    }

    return $response;
}

// TODO generalize to selected post types in options
$post_type = 'post';
add_filter("rest_prepare_{$post_type}", 'qtranxf_rest_prepare', 99, 3);

function qtranxf_rest_request_before_callbacks($response, $handler, $request)
{
    if ($request->get_method() !== 'PUT' && $request->get_method() !== 'POST') {
        return $response;
    }

    $editor_lang = $request->get_param('qtx_editor_lang');
    if (!isset($editor_lang)) {
        return $response;
    }

    $request_body = json_decode($request->get_body(), true);
    $post = get_post($request->get_param('id'), ARRAY_A);

    $fields = ['content', 'title'];
    foreach ($fields as $field) {

        if (isset($request_body[$field])) {

            $new_value = $request_body[$field];

            $original_value = $post['post_' . $field];
            $blocks = qtranxf_get_language_blocks($original_value);
            if (count($blocks) <= 1) {
                continue;
            }

            $split = qtranxf_split_languages($blocks);
            /*
            foreach ( $content as $language => $lang_text ) {
                $lang_text = trim( $lang_text );
                if( ! empty( $lang_text ) ) $result[$language] = $language;
            }*/

            $split[$editor_lang] = $new_value;

            //$sep = '[';
            //$new_data = qtranxf_collect_translations_deep( $split, $sep );
            //$new_data = qtranxf_join_texts( $split, $sep );
            $new_data = qtranxf_join_b($split);

            $request->set_param($field, $new_data);
            //$request_body[ $field ] =  $new_data;
        }
    }
    //$response_data['title']['raw'] = qtranxf_use($editor_lang, $response_data['title']['raw']);
    //$response_data['content']['raw'] = qtranxf_use($editor_lang, $response_data['content']['raw']);
    //$response->set_data( $response_data );


    //$request->set_body( json_encode($request_body) );

    return $response;
}

add_filter('rest_request_before_callbacks', 'qtranxf_rest_request_before_callbacks', 99, 3);

function qtranxf_rest_request_after_callbacks($response, $handler, $request)
{
    if ($request['context'] !== 'edit' || $request->get_method() !== 'PUT' && $request->get_method() !== 'POST') {
        return $response;
    }

    $editor_lang = $request->get_param('qtx_editor_lang');
    if (!isset($editor_lang)) {
        return $response;
    }

    $response_data = $response->get_data();
    if (isset($response_data['content']) && is_array($response_data['content']) && isset($response_data['content']['raw'])) {
        $response_data['title']['raw'] = qtranxf_use($editor_lang, $response_data['title']['raw']);
        $response_data['content']['raw'] = qtranxf_use($editor_lang, $response_data['content']['raw']);
        $response_data['qtx_editor_lang'] = $editor_lang;
        $response->set_data($response_data);
    }

    return $response;
}

add_filter('rest_request_after_callbacks', 'qtranxf_rest_request_after_callbacks', 99, 3);

function qtranxf_enqueue_block_editor_assets()
{
    $script_file = 'js/lib/editor-gutenberg';
    $script_file .= defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.js' : '.min.js';
    wp_register_script(
        'qtx-gutenberg',
        plugins_url($script_file, __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . $script_file),
        true
    );
    wp_enqueue_script('qtx-gutenberg');
}

add_action('enqueue_block_editor_assets', 'qtranxf_enqueue_block_editor_assets');

function qtranxf_admin_loadConfigGutenberg() {
	global $wp_version;
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( version_compare( $wp_version, '5.0' ) >= 0 &&
	     ! ( class_exists( 'Classic_Editor' ) ||
	         is_plugin_active( 'disable-gutenberg/disable-gutenberg.php' ) ||
	         is_plugin_active( 'no-gutenberg/no-gutenberg.php' ) ) ) {
		global $q_config;

		if ($q_config['editor_mode'] == QTX_EDITOR_MODE_LSB) {
			$q_config['editor_mode'] = QTX_EDITOR_MODE_SINGLE;
		}
	}
}

add_action('qtranslate_admin_loadConfig', 'qtranxf_admin_loadConfigGutenberg');

//function qtranxf_enqueue_editor() {
//}
//add_action( 'wp_enqueue_editor', 'qtranxf_enqueue_editor' );


//if ( file_exists(QTRANSLATE_DIR.'/dev/slugs' ) )
//	require_once( QTRANSLATE_DIR.'/dev/slugs/qtx_slug.php' );
