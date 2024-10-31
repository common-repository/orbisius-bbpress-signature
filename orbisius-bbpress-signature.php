<?php
/*
Plugin Name: Orbisius bbPress Signature
Plugin URI: http://club.orbisius.com/products/wordpress-plugins/orbisius-bbpress-signature/
Description: This plugin allows your users to have signatures in your bbPress forum.
Version: 1.0.3
Author: Svetoslav Marinov (Slavi)
Author URI: http://orbisius.com
*/

/*
Copyright 2013 Svetoslav Marinov (Slavi) <slavi@orbisius.com>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Set up plugin
add_action( 'init', 'orbisius_bbpress_signature_init' );

/**
 * Setups loading of assets (css, js)
 * @return type
 */
function orbisius_bbpress_signature_init() {
    if (is_admin()) {
        add_action('admin_menu', 'orbisius_bbpress_signature_setup_admin');

        // when plugins are show add a settings link near my plugin for a quick access to the settings page.
        add_filter('plugin_action_links', 'orbisius_bbpress_signature_settings_link', 10, 2);

        // User Profile Extras
        add_action("show_user_profile", "orbisius_bbpress_signature_profile_fields");
        add_action("edit_user_profile", "orbisius_bbpress_signature_profile_fields");
        add_action("personal_options_update", "orbisius_bbpress_signature_save_profile_fields");
        add_action("edit_user_profile_update", "orbisius_bbpress_signature_save_profile_fields");
    }
    
    // this is where the signature is retrieved.
    add_filter('bbp_get_reply_content', 'orbisius_bbpress_signature_get_forum_signature', 10, 2);
    //add_filter('bbp_get_topic_content', 'orbisius_bbpress_signature_get_forum_signature', 10, 2);
    
	add_action('wp_footer', 'orbisius_bbpress_signature_add_plugin_credits', 1000); // be the last in the footer
}

/**
 * Outputs the signature box in the user profile.
 *
 * @param Obj $user
 * @return void
 */
function orbisius_bbpress_signature_profile_fields($user) {
    if (!current_user_can('edit_user', $user->ID)) {
        return false;
    }

    $forum_signature = get_the_author_meta("_forum_signature", $user->ID);

    echo '<h3>Forum Signature</h3>
    <p>Below you can enter the signature that will be shown under each of your forum posts.</p>
    <table class="form-table">
    <tr>
    <th><label for="_forum_signature">Signature</label></th>
    <td>';
    wp_editor($forum_signature, '_forum_signature', array('teeny' => false, 'media_buttons' => false, 'textarea_rows' => 5));
    echo '</td></tr></table>';
}

/**
 * Saves the user signature.
 *
 * @param int $user_id
 * @return boolean
 */
function orbisius_bbpress_signature_save_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    if (isset($_REQUEST['_forum_signature'])) {
        $forum_signature = $_REQUEST['_forum_signature'];
        $forum_signature = strip_tags($forum_signature, '<p><a><b><strong><br /><span><div><img><li><ol><ul>');
        $forum_signature = trim($forum_signature);
        $forum_signature = substr($forum_signature, 0, 300); // 300 chars max

        update_user_meta($user_id, '_forum_signature', $forum_signature);
    }
}

/**
 * Outputs the signature for a given author. If the signature exists a horizontal line
 * will be inserted before it so the content is nicely separated.
 * @param string $buff
 * @return string
 */
function orbisius_bbpress_signature_get_forum_signature($buff, $reply_id = 0) {
    $post = get_post($reply_id);

    if (empty($post) || empty($post->post_author)) {
        return $buff;
    }

    $author_id = $post->post_author;

    $key = 'orb_bb_sig_' . $author_id; // reduce size of the key
    $cache = wp_cache_get($key);

    if ($cache !== false) {
        return $buff . $cache;
    }

    $user_signature = get_user_meta($author_id, '_forum_signature', true);
    $user_signature = stripslashes($user_signature);

    if (!empty($user_signature)) {
		$powered_by = "<span style='float:right;padding:0 3px;' title='Powered by Orbisius bbPress Signature'>?</span>";
		
		// allow users to remove the ?
		$powered_by = apply_filters('orbisius_bbpress_signature_ext_filter_powered_by_public', $powered_by);
		
        $user_signature = do_shortcode($user_signature); // apply shortcodes (if any)
        $user_signature = "\n<hr class='orbisius-bbpress-signature' style='margin:0;' /> $powered_by " . $user_signature;
        $buff .= $user_signature;
    }

    wp_cache_set($key, $user_signature);

    return $buff;
}

// Add the ? settings link in Plugins page very good
function orbisius_bbpress_signature_settings_link($links, $file) {
    if ($file == plugin_basename(__FILE__)) {
        $settings_link = '<a href="options-general.php?page='
                . dirname(plugin_basename(__FILE__)) . '/' . basename(__FILE__) . '">' . (__("Settings", "Orbisius bbPress Signature")) . '</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}

/**
 * Set up administration
 *
 * @package Orbisius bbPress Signature
 * @since 0.1
 */
function orbisius_bbpress_signature_setup_admin() {
	add_options_page( 'Orbisius bbPress Signature', 'Orbisius bbPress Signature', 5, __FILE__, 'orbisius_bbpress_signature_options_page' );

    // Signature top level menu requires only read access so it will be available to subscribe roles as well.
    add_menu_page('Signature', 'Signature', 'read', dirname(__FILE__) . '-signature',
            'orbisius_bbpress_signature_render_single_sig_box', plugins_url('/i/text_signature.png', __FILE__));
}

/**
 * Renders a separate menu which shows signature box surrounded by a nice form.
 * 
 * @global obj $current_user
 */
function orbisius_bbpress_signature_render_single_sig_box() {
    global $current_user;
    get_currentuserinfo();
    $msg = '';

    if (!empty($_POST)) {
        $nonce = empty($_REQUEST['orbisius_bbpress_signature_nonce']) ? '' : $_REQUEST['orbisius_bbpress_signature_nonce'];

        if (wp_verify_nonce($nonce, basename(__FILE__) . '-action')) {
            orbisius_bbpress_signature_save_profile_fields($current_user->ID);

            $msg = "<div class='updated'><p>Saved</p></div>";
        }
    }

	?>
	<div class="wrap">
        <form method="POST">
            <?php wp_nonce_field( basename(__FILE__) . '-action', 'orbisius_bbpress_signature_nonce' ); ?>
            <?php echo $msg ; ?>
            <?php orbisius_bbpress_signature_profile_fields($current_user); ?>
            <div>
                <input type="submit" name="save" value="Save" class="button-primary" />
            </div>
        </form>
	</div>
	<?php
	
	orbisius_bbpress_signature_add_plugin_credits(1);
}

/**
 * Options page
 *
 * @package Orbisius bbPress Signature
 * @since 1.0
 */
function orbisius_bbpress_signature_options_page() {
	?>
    <?php add_thickbox(); ?>

	<div class="wrap">
        <h2>Orbisius bbPress Signature</h2>

        <h2>What does the plugin do?</h2>
        <p>
            This plugin allows your users to have signatures in a bbPress powered forum.
        </p>

        <h2>Plugin Options</h2>
        <div class="updated">
            <p>Currently, the plugin does not require any configuration options.</p>
        </div>

        <h2>Join the Mailing List</h2>
        <p>
            Get the latest news and updates about this and future cool <a href="http://profiles.wordpress.org/lordspace/"
                                                                            target="_blank" title="Opens a page with the pugins we developed. [New Window/Tab]">plugins we develop</a>.
        </p>

        <p>
            <!-- // MAILCHIMP SUBSCRIBE CODE \\ -->
            1) <a href="http://eepurl.com/guNzr" target="_blank">Subscribe to our newsletter</a> (opens in a new window)
            <!-- \\ MAILCHIMP SUBSCRIBE CODE // -->
            OR
            2) Subscribe using our QR code. [Scan it with your mobile device].<br/>
            <img src="<?php echo plugin_dir_url(__FILE__); ?>/i/guNzr.qr.2.png" alt="" />
        </p>

        <h2>Support</h2>
        
        <div class="updated">
            <p>
            <strong>
            ** NOTE: ** Support is handled on our site: <a href="http://club.orbisius.com/" target="_blank" title="[new window]">http://club.orbisius.com/</a>
            <br/>Please do NOT use the WordPress forums or other places to seek support.
            </strong>
            </p>
        </div>

        <?php if (1) : ?>
            <?php
                $plugin_data = get_plugin_data(__FILE__);

                $app_link = urlencode($plugin_data['PluginURI']);
                $app_title = urlencode($plugin_data['Name']);
                $app_descr = urlencode($plugin_data['Description']);
            ?>
            <h2>Share with friends</h2>
            <p>
                <!-- AddThis Button BEGIN -->
                <div class="addthis_toolbox addthis_default_style addthis_32x32_style">
                    <a class="addthis_button_facebook" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_twitter" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_google_plusone" g:plusone:count="false" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_linkedin" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_email" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_myspace" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_google" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_digg" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_delicious" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_stumbleupon" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_tumblr" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_favorites" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_compact"></a>
                </div>
                <!-- The JS code is in the footer -->

                <script type="text/javascript">
                var addthis_config = {"data_track_clickback":true};
                var addthis_share = {
                  templates: { twitter: 'Check out {{title}} #wordpress #plugin at {{lurl}} (via @orbisius)' }
                }
                </script>
                <!-- AddThis Button START part2 -->
                <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js"></script>
                <!-- AddThis Button END part2 -->
            </p>
            <?php endif ?>

            <h2>Support &amp; Premium Plugins</h2>
            <div class="app-alert-notice">
                <p>
                ** NOTE: ** We have launched our Club Orbisius site: <a href="http://club.orbisius.com/" target="_blank" title="[new window]">http://club.orbisius.com/</a>
                which offers lots of free and premium plugins, video tutorials and more. The support is handled there as well.
                <br/>Please do NOT use the WordPress forums or other places to seek support.
                </p>
            </div>

            <h2>Credits</h2>
            <div>
                Icon for the plugin is by famfamfam
            </div>
        </p>

	</div>
	<?php
}

/**
* adds some HTML comments in the page so people would know that this plugin powers their site.
*/
function orbisius_bbpress_signature_add_plugin_credits($as_html_comment = 0) {
    // pull only these vars
    $default_headers = array(
		'Name' => 'Plugin Name',
		'PluginURI' => 'Plugin URI',
	);

    $plugin_data = get_file_data(__FILE__, $default_headers, 'plugin');

    $url = $plugin_data['PluginURI'];
    $name = $plugin_data['Name'];
    $name_slug = $name;
    $name_slug = strtolower($name_slug);
    $name_slug = preg_replace('#[^\w-]#si', '-', $name_slug);

	$pref = '<!-- ';
	$suff = '-->';
	
	if (!empty($as_html_comment)) {
		$pref = '<br/><div class="updated"><p>';
		$suff = '</p></div>';
        $url_analytics_params = $url;

		$url_analytics_params .= '?' . http_build_query(array(
			'utm_source' => $_SERVER['HTTP_HOST'],
            'utm_medium' => $name_slug . '--admin-powered-by',
            'utm_campaign' => 'in-product-mkt'
		));
		
		$url = "<a href='$url_analytics_params' target='_blank' title='[new tab/window]'>$url</a>";
	}
	
    printf(PHP_EOL . PHP_EOL . $pref . "Powered by $name | URL: $url " . $suff . PHP_EOL . PHP_EOL);
}

