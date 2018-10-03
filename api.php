<?php
/**
Plugin Name: API Plugin
description: Display Github repositories using API 
Version: 1.0
Author: Ahmad Alzoughbi
*/

defined('ABSPATH') or die("Cannot access pages directly.");

require_once 'vendor/autoload.php';

class Github extends \Github\Client {};

function github_func( $atts, $content = null, $github = null ) {
	$options = get_option("github_username");

	$github = new Github();

   	if( empty($options)) {

   		return "<strong>" . __("Username is not defined, you need to define a default username in the options page.", 'github') . "</strong>";

   	} else {

   		$repos = $github->api('user')->repositories($options);

	   	if ( empty($repos) )
	   		return "<strong>" . __("No repos to show", 'github') . "</strong>";
	   	$return = "<ul>";

	   	foreach( $repos as $repo ) {
	   		$return .= "<li>{$repo['name']}</li>";
	   	}
	   	
	   	$return .= "</ul>";

	   	return $return;
	}
};
add_shortcode( "github", "github_func" );

add_action( "admin_menu", "github_menu_func" );
function github_menu_func() {
	add_submenu_page(
		"options-general.php",
		"Github",
		"GitHub",
		"manage_options",
		"github",
		"github_plugin_options"
	);
}

function github_plugin_options() {
	if ( !current_user_can( "manage_options" ) ) {
		wp_die( __( "You do not have sufficient permissions to access this page." ) );
	}

	if ( isset($_GET['status']) && $_GET['status']=='success') { 
	?>
   <div id="message" class="updated notice is-dismissible">
      <p><?php _e("Updated!", "github"); ?></p>
      <button type="button" class="notice-dismiss">
         <span class="screen-reader-text"><?php _e("Dismiss.", "github-api"); ?></span>
      </button>
   </div>
	<?php
	}
 	?>

 	<form method="post" action="<?php echo admin_url( 'admin-post.php'); ?>">
 		<input type="hidden" name="action" value="update_github_settings" />
 		<h3><?php _e("GitHub Info", "github"); ?></h3>
 		<p>
 			<label><?php _e("GitHub Username:", "github"); ?></label>
 			<input class="" type="text" name="github_username" value="<?php echo get_option('github_username'); ?>" />
   		</p>
   		<input class="button button-primary" type="submit" value="<?php _e("Save", "github"); ?>" />
   	</form>
   	<?php
}

add_action( 'admin_post_update_github_settings', 'github_handle_save' );

function github_handle_save() {
	$user = (!empty($_POST['github_username'])) ? $_POST["github_username"] : NULL;
	update_option( "github_username", $user, true );
	$redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=github&status=success";
	header("Location: ".$redirect_url);
	exit;
}