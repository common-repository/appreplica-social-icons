<?php
/*
Plugin Name: Appreplica Social Icons
Plugin URI: http://appreplica.com
Description: Appreplica Social Icons - add your favorite social media links
Author: Appreplica
Version: 1.2
Author URI: http://appreplica.com
*/

# Prevent direct access
if (!defined('ABSPATH')) die('Error!');

# Add scripts/styles
wp_enqueue_style( 'appreplicaicons-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css', array(), '4.4.0' );
wp_enqueue_script('jquery');
wp_enqueue_style( 'wp-color-picker' );
wp_enqueue_script( 'wp-color-picker' );

# Add media scrips
add_action('admin_enqueue_scripts', 'appreplicaicons_admin_enqueue' );
function appreplicaicons_admin_enqueue() {
	wp_enqueue_media();
}

# Register [appreplicaicons] shortcode
add_shortcode( 'appreplicasocialicons', 'embed_appreplicaicons' );

# Function to process content in appreplicaicons shortcode [appreplicaicons][/appreplicaicons]
function embed_appreplicaicons() {
	global $wpdb;
	
	$code = '
    <style>
	.appreplicaicons-css { 
	  width: ' . get_site_option('appreplicaicons-size', 24) . 'px;
	  height: ' .  get_site_option('appreplicaicons-size', 24) . 'px;
	  opacity: ' .  get_site_option('appreplicaicons-opacity', 1) . '; 
	  margin-left: ' .  get_site_option('appreplicaicons-spacing-h', 3) . 'px;
	  margin-right: ' .  get_site_option('appreplicaicons-spacing-h', 3) . 'px;
	  margin-top: ' .  get_site_option('appreplicaicons-spacing-v', 3) . 'px;
	  margin-bottom: ' .  get_site_option('appreplicaicons-spacing-v', 3) . 'px;
	}
	.appreplicaicons-css:hover { opacity: ' . get_site_option('appreplicaicons-opacity-hover', 1) . '; }
	
	.appreplicaicons-other-css { 
	  font-size: ' . get_site_option('appreplicaicons-other-size', 24) . 'px;
	  color: ' .  get_site_option('appreplicaicons-other-color') . ';
	  margin-left: ' .  get_site_option('appreplicaicons-other-spacing-h', 3) . 'px;
	  margin-right: ' .  get_site_option('appreplicaicons-other-spacing-h', 3) . 'px;
	  margin-top: ' .  get_site_option('appreplicaicons-other-spacing-v', 3) . 'px;
	  margin-bottom: ' .  get_site_option('appreplicaicons-other-spacing-v', 3) . 'px;
	  text-decoration: none !important;
	}
	a.appreplicaicons-other-css:link { color: ' . get_site_option('appreplicaicons-other-color') . '; text-decoration: none !important; }
	a.appreplicaicons-other-css:visited { color: ' . get_site_option('appreplicaicons-other-color') . '; text-decoration: none !important; }
	a.appreplicaicons-other-css:hover { color: ' . get_site_option('appreplicaicons-other-color-hover') . '; text-decoration: none !important; }
	</style>
	';

	# Create an array for items to output
	include ("appreplica-social-icons-list.php");
	$i = 0;
	foreach ($appreplicaicons as $value) {
		
		$iconName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
		
		if (get_site_option($iconName) == 1) {
			
			$icon[$i]['Url'] = $iconName . '_url';
			$icon[$i]['tooltip'] = $iconName . '_text';
			$icon[$i]['target'] = $iconName . '_target';
			$icon[$i]['order'] = $iconName . '_order';
			
			$icon[$i]['name'] = $value[0];
			$icon[$i]['folder'] = $value[1];
			$icon[$i]['UrlVal'] = get_site_option($icon[$i]['Url']);
			$icon[$i]['tooltipVal'] = get_site_option($icon[$i]['tooltip']);
			$icon[$i]['targetVal'] = get_site_option($icon[$i]['target'],0);
			$icon[$i]['orderVal'] = get_site_option($icon[$i]['order'],0);
			
			$i++;
			
		}
	}

	// add custom icons to array
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$table_name = $wpdb->prefix . "appreplica_social_icons";
	$sql = "SELECT * FROM " . $table_name . " WHERE 1";
	$iconList = $wpdb->get_results($sql);
	foreach($iconList as $item) {
	
		$iconName = 'appreplicaicons_icon_custom_' . $item->id;
		$icon[$i]['Url'] = $iconName . '_url';
		$icon[$i]['tooltip'] = $iconName . '_text';
		$icon[$i]['target'] = $iconName . '_target';
		$icon[$i]['order'] = $iconName . '_order';
		
		$icon[$i]['name'] = $item->ar_title;
		$icon[$i]['folder'] = 999; // custom icon flag
		$icon[$i]['UrlVal'] = get_site_option($icon[$i]['Url']);
		$icon[$i]['ImgUrl'] = $item->ar_imageurl;
		$icon[$i]['tooltipVal'] = get_site_option($icon[$i]['tooltip']);
		$icon[$i]['targetVal'] = get_site_option($icon[$i]['target'],0);
		$icon[$i]['orderVal'] = get_site_option($icon[$i]['order'],0);
		
		$i++;
		
	}
	
	// sort according to custom order
	foreach ($icon as $key => $row) {
		$order[$key] = $row['orderVal'];
	}
	array_multisort($order, SORT_ASC, $icon);
	
	# Output icons List
	for ($j = 0 ; $j < count($icon) ; $j++) {
		  
		  if ($icon[$j]['targetVal'] == 1) { $target = '_blank'; } else { $target = '_self'; }
		
		  # code
		  if ($icon[$j]['folder'] <= 3) {
			  $code .= '<a target="' . $target . '" title="' .$icon[$j]['tooltipVal'] . '" href="' . $icon[$j]['UrlVal'] . '">';
			  $code .= '<img class="appreplicaicons-css" src="' . plugins_url( 'icons' . '/' . $icon[$j]['folder'] . '/' . $icon[$j]['name'] . '.png', __FILE__ ) . '"' . ' alt="" />';
			  $code .= '</a>';
		  }
		  if ($icon[$j]['folder'] == 99) {
			  $code .= '<a class="appreplicaicons-other-css" target="' . $target . '" title="' . $icon[$j]['tooltipVal'] . '" href="' . $icon[$j]['UrlVal'] . '">';
			  $code .= '<i class="fa fa-' . $icon[$j]['name'] . ' fa-fw"></i>';
			  $code .= '</a>';
		  }
		  if ($icon[$j]['folder'] == 999) {
			  $code .= '<a class="appreplicaicons-css" target="' . $target . '" title="' . $icon[$j]['tooltipVal'] . '" href="' . $icon[$j]['UrlVal'] . '">';
			  $code .= '<img class="appreplicaicons-css" src="' . $icon[$j]['ImgUrl'] . '"' . ' alt="" />';
			  $code .= '</a>';
		  }
	
	}
	
	# Return code
    return $code;
}


# Create appreplicaicons settings menu for admin
add_action( 'admin_menu', 'appreplicaicons_create_menu' );
add_action( 'network_admin_menu', 'appreplicaicons_network_admin_create_menu' );

# Create link to plugin options page from plugins list
function appreplicaicons_plugin_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=appreplicaicons_settings_page">Settings</a>';
    array_push( $links, $settings_link );
    return $links;
}

$appreplicaicons_plugin_basename = plugin_basename( __FILE__ );
add_filter( 'plugin_action_links_' . $appreplicaicons_plugin_basename, 'appreplicaicons_plugin_add_settings_link' );

# Create new top level menu for sites
function appreplicaicons_create_menu() {
    add_menu_page('Appreplica Options', 'Icons', 'install_plugins', 'appreplicaicons_settings_page', 'appreplicaicons_settings_page');
}

# Create new top level menu for network admin
function appreplicaicons_network_admin_create_menu() {
    add_menu_page('Appreplica Options', 'Icons', 'manage_options', 'appreplicaicons_settings_page', 'appreplicaicons_settings_page');
}

function appreplicaicons_update_option($name, $value) {
    return is_multisite() ? update_site_option($name, $value) : update_option($name, $value);
}

# Define widget
class AppreplicaIcons_Widget extends WP_Widget {

	# Register widget with WordPress
	function __construct() {
		parent::__construct(
			'appreplicaicons_widget', // Base ID
			'Appreplica Social Icons', // Name
			array( 'description' => __( 'Add social icons to your widgets sections' ), ) // Args
		);
	}

	# Frontend display of widget
	public function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if (!empty($title)) { echo $before_title . $title . $after_title; }
		echo embed_appreplicaicons();
		echo $after_widget;
	}
	
	# Backend widget form.
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}

} // end class Widget

# Register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "AppreplicaIcons_Widget" );' ) );

# Create table for custom uploaded icons
function appreplicaicons_database_setup () {
	global $wpdb;
	$table_name = $wpdb->prefix . "appreplica_social_icons";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql2 = "CREATE TABLE `$table_name` (
		`id` BIGINT(20) NOT NULL AUTO_INCREMENT, 
		`ar_title` VARCHAR(255) NULL, 
		`ar_imageurl` VARCHAR(255) NOT NULL, 
		PRIMARY KEY (`id`)) ENGINE = InnoDB;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql2);
	}
}
register_activation_hook(__FILE__,'appreplicaicons_database_setup');

function appreplicaicons_settings_page() {

?>

<div id="appreplicaicons_admin" class="wrap">

<div style="padding-bottom: 10px;">
<h1>Appreplica Social Icons (v.1.2)</h1>
</div>

<?php $appreplicaicons_active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'configure'; ?>
<h2 class="nav-tab-wrapper">
<a href="?page=appreplicaicons_settings_page&amp;tab=configure" class="nav-tab <?php echo $appreplicaicons_active_tab == 'configure' ? 'nav-tab-active' : ''; ?>">Configure</a>
<a href="?page=appreplicaicons_settings_page&amp;tab=icons" class="nav-tab <?php echo $appreplicaicons_active_tab == 'icons' ? 'nav-tab-active' : ''; ?>">Icons</a>
<a href="?page=appreplicaicons_settings_page&amp;tab=upload" class="nav-tab <?php echo $appreplicaicons_active_tab == 'upload' ? 'nav-tab-active' : ''; ?>">Upload</a>
<a href="?page=appreplicaicons_settings_page&amp;tab=style" class="nav-tab <?php echo $appreplicaicons_active_tab == 'style' ? 'nav-tab-active' : ''; ?>">Size / Color</a>
<a href="?page=appreplicaicons_settings_page&amp;tab=support" class="nav-tab <?php echo $appreplicaicons_active_tab == 'support' ? 'nav-tab-active' : ''; ?>">Support / FAQ</a>
</h2>





<?php if( $appreplicaicons_active_tab == 'configure' ) { // Configure Tab ?>

<form name="form1" method="post" action="">
    
<?php
if (isset($_POST['_wpnonce']) && isset($_POST['submit'])) {

	include ("appreplica-social-icons-list.php");
	
	foreach ($appreplicaicons as $value) {
		$varName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
		$varNameUrl = $varName . '_url';
		$varNametext = $varName . '_text';
		$varNametarget = $varName . '_target';
		$varNameorder = $varName . '_order';
		appreplicaicons_update_option($varNameUrl, $_POST[$varNameUrl]);
		appreplicaicons_update_option($varNametext, $_POST[$varNametext]);
		appreplicaicons_update_option($varNametarget, intval($_POST[$varNametarget]));
		appreplicaicons_update_option($varNameorder, intval($_POST[$varNameorder]));
	}
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$table_name = $wpdb->prefix . "appreplica_social_icons";
	$sql = "SELECT * FROM " . $table_name . " WHERE 1";
	$iconList = $wpdb->get_results($sql);
	foreach($iconList as $item) {
		$varName = 'appreplicaicons_icon_custom_' . $item->id;
		$varNameUrl = $varName . '_url';
		$varNametext = $varName . '_text';
		$varNametarget = $varName . '_target';
		$varNameorder = $varName . '_order';
		appreplicaicons_update_option($varNameUrl, $_POST[$varNameUrl]);
		appreplicaicons_update_option($varNametext, $_POST[$varNametext]);
		appreplicaicons_update_option($varNametarget, intval($_POST[$varNametarget]));
		appreplicaicons_update_option($varNameorder, intval($_POST[$varNameorder]));
	}

	$confirmSave = 1;
}
wp_nonce_field('form-settings');
?>

<style>
#appreplicaicons_admin table.appreplicaicons_iconlist_table{
	border-collapse: collapse;
	max-width: 900px;
}
#appreplicaicons_admin table.appreplicaicons_iconlist_table th {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: left;
  font-size: 15px;
}
#appreplicaicons_admin table.appreplicaicons_iconlist_table td{
  border: 1px solid #ccc;
  padding: 10px;
  text-align: left;
  font-size: 13px;
}
#appreplicaicons_admin table.appreplicaicons_iconlist_table th{
	background: rgba(0,0,0,0.1);
}
#appreplicaicons_admin table.appreplicaicons_iconlist_table td{
	background: rgba(255,255,255,0.5);
}
#appreplicaicons_admin .appreplicaicons_table_header{
	background: #ddd;
	font-weight: bold;
	color: #999;
}
</style>

<?php if ($confirmSave) { echo '<br /><div style="padding: 10px; font-size: 18px; color: #ff0000;"><b>Your changes have been saved</b></div>'; } ?>

<?php 
// get count
include ("appreplica-social-icons-list.php");
$count = 0;
foreach ($appreplicaicons as $value) {
	$varName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
	if (get_site_option($varName) == 1) { $count++; }
}
?>

<?php if ($count == 0) { ?>
<div style="max-width: 900px; padding: 0px; margin-left: 50px; margin-top: 100px; margin-bottom: 200px;">
<h1>
Welcome to Appreplica Social Icons! Add custom icons to your widgets sections.<br />
<span style="font-size: 18px; color: #999999;">Please select one or more icons from the Icons tab to get started.</span>
</h1>
</div>
<?php } ?>

<?php if ($count > 0) { ?>
<table class="appreplicaicons_iconlist_table">
<tbody>

<div style="padding-top: 15px;">&nbsp;</div>

<tr valign="top">
  <th scope="row">Icon</th>
  <th scope="row">URL</th>
  <th scope="row" nowrap>Tooltip Text</th>
  <th scope="row" nowrap>New Window</th>
  <th scope="row" nowrap>Order</th>
</tr>

<?php 

# Create an Array for items to output
include ("appreplica-social-icons-list.php");
$i = 0;
foreach ($appreplicaicons as $value) {
	
	$iconName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
	if (get_site_option($iconName) == 1) {
		
		$icon[$i]['Url'] = $iconName . '_url';
		$icon[$i]['tooltip'] = $iconName . '_text';
		$icon[$i]['target'] = $iconName . '_target';
		$icon[$i]['order'] = $iconName . '_order';
		
		$icon[$i]['name'] = $value[0];
		$icon[$i]['folder'] = $value[1];
		$icon[$i]['UrlVal'] = get_site_option($icon[$i]['Url']);
		$icon[$i]['tooltipVal'] = get_site_option($icon[$i]['tooltip']);
		$icon[$i]['targetVal'] = get_site_option($icon[$i]['target'],0);
		$icon[$i]['orderVal'] = get_site_option($icon[$i]['order'],0);
		
		$i++;
		
	}
}

// add custom icons to array
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
$table_name = $wpdb->prefix . "appreplica_social_icons";
$sql = "SELECT * FROM " . $table_name . " WHERE 1";
$iconList = $wpdb->get_results($sql);
foreach($iconList as $item) {

	$iconName = 'appreplicaicons_icon_custom_' . $item->id;
	$icon[$i]['Url'] = $iconName . '_url';
	$icon[$i]['tooltip'] = $iconName . '_text';
	$icon[$i]['target'] = $iconName . '_target';
	$icon[$i]['order'] = $iconName . '_order';
	
	$icon[$i]['name'] = $item->ar_title;
	$icon[$i]['folder'] = 999; // custom icon flag
	$icon[$i]['UrlVal'] = get_site_option($icon[$i]['Url']);
	$icon[$i]['ImgUrl'] = $item->ar_imageurl;
	$icon[$i]['tooltipVal'] = get_site_option($icon[$i]['tooltip']);
	$icon[$i]['targetVal'] = get_site_option($icon[$i]['target'],0);
	$icon[$i]['orderVal'] = get_site_option($icon[$i]['order'],0);
	
	$i++;
	
}

// sort according to custom order
foreach ($icon as $key => $row) {
    $order[$key] = $row['orderVal'];
}
array_multisort($order, SORT_ASC, $icon);

	
for ($j = 0 ; $j < count($icon) ; $j++) {

if ($icon[$j]['targetVal'] == 1) { $checked = 'checked'; } else { $checked = ''; }

if ($icon[$j]['folder'] <= 3) {
?>
<tr valign="top">
<td style="text-align: center;"><img src="<?php echo plugins_url( 'icons' . '/' . $icon[$j]['folder'] . '/' . $icon[$j]['name'] . '.png', __FILE__ ); ?>" width="28" height="28" align="absmiddle" alt="" /></td>
<td><input type="text" style="width: 425px; font-size: 14px;" name="<?php echo $icon[$j]['Url']; ?>" value="<?php echo $icon[$j]['UrlVal']; ?>" placeholder="URL to Open (<?php echo $icon[$j]['name']; ?>)" /></td>
<td><input type="text" style="width: 225px; font-size: 14px;" name="<?php echo $icon[$j]['tooltip']; ?>" value="<?php echo $icon[$j]['tooltipVal']; ?>" placeholder="Tooltip Text" /></td>
<td style="text-align: center;"><input type="checkbox" name="<?php echo $icon[$j]['target']; ?>" value="1" <?php echo $checked; ?>></td>
<td style="text-align: center;"><input type="text" maxlength="3" style="width: 40px; font-size: 14px;" name="<?php echo $icon[$j]['order']; ?>" value="<?php echo $icon[$j]['orderVal']; ?>" placeholder="" /></td>
</tr>
<?php } elseif ($icon[$j]['folder'] == 99) { ?>
<tr valign="top">
<td style="text-align: center;"><i class="fa fa-<?php echo $icon[$j]['name']; ?> fa-2x fa-fw"></i></td>
<td><input type="text" style="width: 425px; font-size: 14px;" name="<?php echo $icon[$j]['Url']; ?>" value="<?php echo $icon[$j]['UrlVal']; ?>" placeholder="URL to Open (<?php echo $icon[$j]['name']; ?>)" /></td>
<td><input type="text" style="width: 225px; font-size: 14px;" name="<?php echo $icon[$j]['tooltip']; ?>" value="<?php echo $icon[$j]['tooltipVal']; ?>" placeholder="Tooltip Text" /></td>
<td style="text-align: center;"><input type="checkbox" name="<?php echo $icon[$j]['target']; ?>" value="1" <?php echo $checked; ?>></td>
<td style="text-align: center;"><input type="text" maxlength="3" style="width: 40px; font-size: 14px;" name="<?php echo $icon[$j]['order']; ?>" value="<?php echo $icon[$j]['orderVal']; ?>" placeholder="" /></td>
</tr>
<?php } elseif ($icon[$j]['folder'] == 999) { ?>
<tr valign="top">
<td style="text-align: center;"><img src="<?php echo $icon[$j]['ImgUrl']; ?>" width="28" height="28" align="absmiddle" alt="" /></td>
<td><input type="text" style="width: 425px; font-size: 14px;" name="<?php echo $icon[$j]['Url']; ?>" value="<?php echo $icon[$j]['UrlVal']; ?>" placeholder="URL to Open (<?php echo $icon[$j]['name']; ?>)" /></td>
<td><input type="text" style="width: 225px; font-size: 14px;" name="<?php echo $icon[$j]['tooltip']; ?>" value="<?php echo $icon[$j]['tooltipVal']; ?>" placeholder="Tooltip Text" /></td>
<td style="text-align: center;"><input type="checkbox" name="<?php echo $icon[$j]['target']; ?>" value="1" <?php echo $checked; ?>></td>
<td style="text-align: center;"><input type="text" maxlength="3" style="width: 40px; font-size: 14px;" name="<?php echo $icon[$j]['order']; ?>" value="<?php echo $icon[$j]['orderVal']; ?>" placeholder="" /></td>
</tr>
<?php } } ?>

</tbody>
</table>

<?php submit_button(); ?>
<hr style="border: none; border-bottom: 1px solid #ccc;" />

<?php } ?>

</form>

<br /><br /><br />

<?php } // End Configure Tab ?>





<?php if( $appreplicaicons_active_tab == 'icons' ) { // Icons Tab ?>

<form name="form2" method="post" action="">
    
<?php
if (isset($_POST['_wpnonce']) && isset($_POST['submit'])) {

	include ("appreplica-social-icons-list.php");
	foreach ($appreplicaicons as $value) {
		$varName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
		appreplicaicons_update_option($varName, intval($_POST[$varName]));
	}
	$confirmSave = 1;
}
wp_nonce_field('form-settings');
?>

<style>
#appreplicaicons_admin .appreplicaicons-icon-box {
float: left;
width: 125px;
min-height: 70px;
height: auto;
margin: 5px;
padding: 3px;
padding-top: 10px;
background-color: #e4e4e4;
border: 1px solid #ccc;
border-radius: 3px;
}
#appreplicaicons_admin .checked {
background-color: #f6f7d8;
}
</style>

<table class="form-table">
<tbody>

<tr>
<td>

<h3>
Please select / deselect the icons you wish to include and then use the Configure tab to specify the URLs.
<div style="font-size: 16px; color: #999;">Don't forget to click Save Changes after making your selections!</div>
</h3>

<?php if ($confirmSave) { echo '<br /><div style="font-size: 18px; color: #ff0000;"><b>Your changes have been saved</b></div><br />'; } ?>
<?php submit_button(); ?>

</td>
</tr>

<tr>
<td>

<div style="max-width: 1400px;">
<h3 style="color: #999;">&nbsp;Appreplica Square Icons</h3>
<?php 
include ("appreplica-social-icons-list.php");
foreach ($appreplicaicons as $value) {
$varName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
if (get_site_option($varName) == 1) { $checked = 'checked'; } else { $checked = ''; }
if ($value[1] == 0) {
?>
<div class="appreplicaicons-icon-box <?php echo $checked; ?>">
<label>
<table>
<tr><td width="1%" style="padding: 2px; text-align: center;"><img src="<?php echo plugins_url( 'icons' . '/' . $value[1] . '/' . $value[0] . '.png', __FILE__ ); ?>" width="45" height="45" align="absmiddle" alt=""/></td></tr>
<tr><td style="padding: 2px; text-align: center; font-size: 12px; color: #666;"><?php echo $value[0]; ?></td></tr>
<tr><td style="padding: 2px; text-align: center;"><input type="checkbox" name="<?php echo $varName; ?>" value="1" <?php echo $checked; ?> ></td></tr>
</table>
</label>
</div>
<?php
}
}
?>
</div>

</td>
</tr>

<tr>
<td>

<div style="max-width: 1400px;">
<h3 style="color: #999;">&nbsp;Flat Social Icons</h3>
<?php 
foreach ($appreplicaicons as $value) {
$varName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
if (get_site_option($varName) == 1) { $checked = 'checked'; } else { $checked = ''; }
if ($value[1] == 1) {
?>
<div class="appreplicaicons-icon-box <?php echo $checked; ?>">
<label>
<table>
<tr><td width="1%" style="padding: 2px; text-align: center;"><img src="<?php echo plugins_url( 'icons' . '/' . $value[1] . '/' . $value[0] . '.png', __FILE__ ); ?>" width="45" height="45" align="absmiddle" alt=""/></td></tr>
<tr><td style="padding: 2px; text-align: center; font-size: 12px; color: #666;"><?php echo $value[0]; ?></td></tr>
<tr><td style="padding: 2px; text-align: center;"><input type="checkbox" name="<?php echo $varName; ?>" value="1" <?php echo $checked; ?> ></td></tr>
</table>
</label>
</div>
<?php
}
}
?>
</div>

</td>
</tr>

<tr>
<td>

<div style="max-width: 1400px;">
<h3 style="color: #999;">&nbsp;Shadow Social Icons</h3>
<?php 
foreach ($appreplicaicons as $value) {
$varName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
if (get_site_option($varName) == 1) { $checked = 'checked'; } else { $checked = ''; }
if ($value[1] == 2) {
?>
<div class="appreplicaicons-icon-box <?php echo $checked; ?>">
<label>
<table>
<tr><td width="1%" style="padding: 2px; text-align: center;"><img src="<?php echo plugins_url( 'icons' . '/' . $value[1] . '/' . $value[0] . '.png', __FILE__ ); ?>" width="45" height="45" align="absmiddle" alt=""/></td></tr>
<tr><td style="padding: 2px; text-align: center; font-size: 12px; color: #666;"><?php echo $value[0]; ?></td></tr>
<tr><td style="padding: 2px; text-align: center;"><input type="checkbox" name="<?php echo $varName; ?>" value="1" <?php echo $checked; ?> ></td></tr>
</table>
</label>
</div>
<?php
}
}
?>
</div>

</td>
</tr>

<tr>
<td>

<div style="max-width: 1400px;">
<h3 style="color: #999;">&nbsp;Gloss Social Icons</h3>
<?php 
foreach ($appreplicaicons as $value) {
$varName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
if (get_site_option($varName) == 1) { $checked = 'checked'; } else { $checked = ''; }
if ($value[1] == 3) {
?>
<div class="appreplicaicons-icon-box <?php echo $checked; ?>">
<label>
<table>
<tr><td width="1%" style="padding: 2px; text-align: center;"><img src="<?php echo plugins_url( 'icons' . '/' . $value[1] . '/' . $value[0] . '.png', __FILE__ ); ?>" width="45" height="45" align="absmiddle" alt=""/></td></tr>
<tr><td style="padding: 2px; text-align: center; font-size: 12px; color: #666;"><?php echo $value[0]; ?></td></tr>
<tr><td style="padding: 2px; text-align: center;"><input type="checkbox" name="<?php echo $varName; ?>" value="1" <?php echo $checked; ?> ></td></tr>
</table>
</label>
</div>
<?php
}
}
?>
</div>

</td>
</tr>

<tr>
<td>

<div style="max-width: 1400px;">
<h3 style="color: #999;">&nbsp;Single Color Brand Icons</h3>
<?php 
foreach ($appreplicaicons as $value) {
$varName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
if (get_site_option($varName) == 1) { $checked = 'checked'; } else { $checked = ''; }
if ($value[1] == 99) {
?>
<label>
<div class="appreplicaicons-icon-box <?php echo $checked; ?>">
<table>
<tr><td width="1%" style="padding: 2px; text-align: center;"><i class="fa fa-<?php echo $value[0]; ?> fa-3x fa-fw"></i></td></tr>
<tr><td style="padding: 2px; text-align: center; font-size: 12px; color: #666;"><?php echo $value[0]; ?></td></tr>
<tr><td style="padding: 2px; text-align: center;"><input type="checkbox" name="<?php echo $varName; ?>" value="1" <?php echo $checked; ?> ></td></tr>
</table>
</div>
</label>
<?php
}
}
?>
</div>

</td>
</tr>

</tbody>
</table>

<?php submit_button(); ?>

</form>

<br /><br /><br />
<hr style="border: none; border-bottom: 1px solid #ccc;" />

<?php } // End Icons Tab ?>






<?php if( $appreplicaicons_active_tab == 'upload' ) { // Upload Tab ?>

<form name="form3" method="post" action="">

<style>
#appreplicaicons_admin table.appreplicaicons_iconlist_table{
  border-collapse: collapse;
  max-width: 900px;
  margin: 10px;
}
#appreplicaicons_admin table.appreplicaicons_iconlist_table th {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: left;
  font-size: 15px;
}
#appreplicaicons_admin table.appreplicaicons_iconlist_table td{
  border: 1px solid #ccc;
  padding: 10px;
  color: #666;
  text-align: left;
  font-size: 13px;
}
#appreplicaicons_admin table.appreplicaicons_iconlist_table th{
	background: rgba(0,0,0,0.1);
}
#appreplicaicons_admin table.appreplicaicons_iconlist_table td{
	background: rgba(255,255,255,0.5);
}
#appreplicaicons_admin .appreplicaicons_table_header{
	background: #ddd;
	font-weight: bold;
	color: #999;
}
a.appreplicaicons-link:link { color: #666; text-decoration: none;}
a.appreplicaicons-link:visited { color: #666; }
a.appreplicaicons-link:hover { color: #777; }
</style>

<?php
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
if (isset($_POST['_wpnonce']) && isset($_POST['submit'])) {
	
  if (strlen($_POST['icon_title']) > 0 && strlen($_POST['icon_url']) > 0) {
  $table_name = $wpdb->prefix . "appreplica_social_icons";
  $results = $wpdb->insert( 
	  $table_name, 
	  array( 
		  'ar_title' => sanitize_text_field($_POST['icon_title']), 
		  'ar_imageurl' => sanitize_text_field($_POST['icon_url']), 
	  ), 
	  array( 
		  '%s', 
		  '%s',
	  ) 
  );
  if (!$results) { $confirmSave = 0; } else { $confirmSave = 1; }
  } else {
	  $err = 1;
  }
}

// delete icon
if ($_GET['d'] && $_GET['id']) { 
  $table_name = $wpdb->prefix . "appreplica_social_icons";
  $wpdb->delete( $table_name, array( 'id' => $_GET['id'] ), array( '%d' ) );
}

wp_nonce_field('form-settings');
?>

<div style="padding: 15px;">
<h3>
<?php if ($err == 1) { ?>
<div style="font-size: 20px; padding-top: 5px; color: #ff0000;">Please enter the icon name and select the icon image.</div><br />
<?php } ?>
Upload your own icons (only square icons are supported)<br />
</h3>
</div>


<table class="appreplicaicons_iconlist_table">
<tbody>

<tr valign="top">
  <th scope="row">Name</th>
  <th scope="row">Image URL</th>
  <th scope="row" style="text-align: center;">Icon</th>
  <th scope="row">&nbsp;</th>
</tr>

<?php 
// get current entries
$table_name = $wpdb->prefix . "appreplica_social_icons";
$sql = "SELECT * FROM " . $table_name . " WHERE 1";
$iconList = $wpdb->get_results($sql);
foreach($iconList as $icon) {
?>
<tr>
<td><?php echo $icon->ar_title; ?></td>
<td><?php echo $icon->ar_imageurl; ?></td>
<td style="width: 70px; text-align: center;"><img src="<?php echo $icon->ar_imageurl; ?>" border="0" width="30" height="30" alt="" /></td>
<td style="text-align: center;"><a class="appreplicaicons-link" title="Delete Icon" href="?page=appreplicaicons_settings_page&amp;tab=upload&amp;id=<?php echo $icon->id; ?>&amp;d=y"><i class="fa fa-trash fa-2x"></i></a></td>
</tr>
<?php 
}
?>

<tr>
<td><input type="text" style="width: 150px; font-size: 14px;" name="icon_title" value="" placeholder="Icon Name" /></td>
<td><input type="text" style="width: 400px; font-size: 14px;" name="icon_url" id="icon_url" value="" placeholder="Image URL" /></td>
<td style="width: 70px; text-align: center;"><img id="icon_preview" src="" style="border: 0px;" width="30" height="30" alt="" /></td>
<td><input id="logo_image_button" class="button" type="button" value="Add Icon" /></td>
</tr>

</tbody>
</table>

<div style="padding: 15px;">
<?php submit_button(); ?>
</div>

</form>

<br /><br /><br />
<hr style="border: none; border-bottom: 1px solid #ccc;" />

<script>
jQuery(document).ready(function($) {
	
	var custom_logo_uploader;
	
	$('#logo_image_button').click(function(e) {
        e.preventDefault();
        // Reopen if media frame already exists
        if (custom_logo_uploader) {
            custom_logo_uploader.open();
            return;
        }
        // Create media frame
        custom_logo_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Upload/Select Icon',
            button: {
              text: 'Set Icon Image'
            },
            multiple: false
        });
        // Set URL upon selection
        custom_logo_uploader.on('select', function() {
            attachment = custom_logo_uploader.state().get('selection').first().toJSON();
            $('#icon_url').val(attachment.url);
			//$('#icon_preview').attr('width','32');
			//$('#icon_preview').attr('height','32');
			$('#icon_preview').attr('src',attachment.url);
			
        });
        // Show media selector
        custom_logo_uploader.open();
    });
	
});
</script>

<?php } // End Upload Tab ?>






<?php if( $appreplicaicons_active_tab == 'style' ) { // Style Tab ?>

<style>
#appreplicaicons_admin table.appreplicaicons_style_table{
  border-collapse: collapse;
  min-width: 600px;
  max-width: 1200px;
  margin-left: 20px;
}
#appreplicaicons_admin table.appreplicaicons_style_table td{
  padding: 7px;
  text-align: left;
  font-size: 14px;
}
</style>

<form name="form1" method="post" action="">
    
<?php

if (isset($_POST['_wpnonce']) && isset($_POST['submit'])) {
	
	appreplica_update_option('appreplicaicons-size', max(intval($_POST['appreplicaicons-size']),1));
	appreplica_update_option('appreplicaicons-spacing-h', max(min(intval($_POST['appreplicaicons-spacing-h']),25),0));
	appreplica_update_option('appreplicaicons-spacing-v', max(min(intval($_POST['appreplicaicons-spacing-v']),25),0));
	appreplica_update_option('appreplicaicons-opacity', max(min(floatval($_POST['appreplicaicons-opacity']),1),0));
	appreplica_update_option('appreplicaicons-opacity-hover', max(min(floatval($_POST['appreplicaicons-opacity-hover']),1),0));
	
	appreplica_update_option('appreplicaicons-other-size', max(intval($_POST['appreplicaicons-other-size']),1));
	appreplica_update_option('appreplicaicons-other-spacing-h', max(min(intval($_POST['appreplicaicons-other-spacing-h']),25),0));
	appreplica_update_option('appreplicaicons-other-spacing-v', max(min(intval($_POST['appreplicaicons-other-spacing-v']),25),0));
	appreplica_update_option('appreplicaicons-other-color', $_POST['appreplicaicons-other-color']);
	appreplica_update_option('appreplicaicons-other-color-hover', $_POST['appreplicaicons-other-color-hover']);
	
	$confirmSave = 1;
}
wp_nonce_field('form-settings');
?>

<br />

<?php if ($confirmSave) { echo '<br /><div style="padding: 10px; font-size: 18px; color: #ff0000;"><b>Your changes have been saved</b></div>'; } ?>

<table class="appreplicaicons_style_table">

<tr><td colspan="2"><h3>Color Icons Size and Opacity</h3></td></tr>

<tr>
<td style="width: 175px;">&nbsp;&nbsp;&nbsp;<b>Height / Width in Pixels</b></td>
<td><input type="text" maxlength="3" style="width: 65px; font-size: 16px;" name="appreplicaicons-size" value="<?php echo get_site_option('appreplicaicons-size', 24); ?>"  placeholder="" />&nbsp; <i style="color: #999;">A number between 1 and 200</i></td>
</tr>

<tr>
<td style="width: 175px;">&nbsp;&nbsp;&nbsp;<b>Horizontal Spacing</b></td>
<td><input type="text" maxlength="3" style="width: 65px; font-size: 16px;" name="appreplicaicons-spacing-h" value="<?php echo get_site_option('appreplicaicons-spacing-h', 3); ?>"  placeholder="" />&nbsp; <i style="color: #999;">A number between 0 and 25</i></td>
</tr>

<tr>
<td style="width: 175px;">&nbsp;&nbsp;&nbsp;<b>Vertical Spacing</b></td>
<td><input type="text" maxlength="3" style="width: 65px; font-size: 16px;" name="appreplicaicons-spacing-v" value="<?php echo get_site_option('appreplicaicons-spacing-v', 3); ?>"  placeholder="" />&nbsp; <i style="color: #999;">A number between 0 and 25</i></td>
</tr>

<tr>
<td>&nbsp;&nbsp;&nbsp;<b>Opacity</b></td>
<td><input type="text" maxlength="3" style="width: 65px; font-size: 16px;" name="appreplicaicons-opacity" value="<?php echo get_site_option('appreplicaicons-opacity', 1); ?>"  placeholder="" /> &nbsp; <i style="color: #999;">A number between 0 and 1</i></td>
</tr>

<tr>
<td>&nbsp;&nbsp;&nbsp;<b>Opacity on Hover</b></td>
<td><input type="text" maxlength="3" style="width: 65px; font-size: 16px;" name="appreplicaicons-opacity-hover" value="<?php echo get_site_option('appreplicaicons-opacity-hover', 1); ?>"  placeholder="" /> &nbsp; <i style="color: #999;">A number between 0 and 1</i></td>
</tr>

<tr>
<td>&nbsp;&nbsp;&nbsp;<b>Preview</b></td>
<td>

<style>
.appreplicaicons-preview { 
	width: <?php echo get_site_option('appreplicaicons-size', 24); ?>px;
	height: <?php echo get_site_option('appreplicaicons-size', 24); ?>px;
	opacity: <?php echo get_site_option('appreplicaicons-opacity', 1); ?>; 
	margin-left: <?php echo get_site_option('appreplicaicons-spacing-h', 3); ?>px; 
	margin-right: <?php echo get_site_option('appreplicaicons-spacing-h', 3); ?>px; 
	margin-top: <?php echo get_site_option('appreplicaicons-spacing-v', 3); ?>px; 
	margin-bottom: <?php echo get_site_option('appreplicaicons-spacing-v', 3); ?>px; 
}
.appreplicaicons-preview:hover { opacity: <?php echo get_site_option('appreplicaicons-opacity-hover', 1); ?>; }
</style>

<?php 
include ("appreplica-social-icons-list.php");
foreach ($appreplicaicons as $value) {
$varName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
if (get_site_option($varName) == 1) {
if ($value[1] <= 3) {
?>
<img class="appreplicaicons-preview" src="<?php echo plugins_url( 'icons' . '/' . $value[1] . '/' . $value[0] . '.png', __FILE__ ); ?>" alt=""/>
<?php } } } ?>

<?php 
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
$table_name = $wpdb->prefix . "appreplica_social_icons";
$sql = "SELECT * FROM " . $table_name . " WHERE 1";
$iconList = $wpdb->get_results($sql);
foreach($iconList as $item) {
?>
<img class="appreplicaicons-preview" src="<?php echo $item->ar_imageurl; ?>" alt=""/>
<?php } ?> 
</td>
</tr>

<tr><td colspan="2"><br /><hr style="border: none; border-bottom: 1px solid #ddd;" /></td></tr>

<tr><td colspan="2"><h3>Single Color Brand Icons Size and Colors</h3></td></tr>

<tr>
<td>&nbsp;&nbsp;&nbsp;<b>Height / Width in Pixels</b></td>
<td><input type="text" maxlength="3" style="width: 65px; font-size: 16px;" name="appreplicaicons-other-size" value="<?php echo get_site_option('appreplicaicons-other-size', 24); ?>"  placeholder="" />&nbsp; <i style="color: #999;">A number between 1 and 200</i></td>
</tr>

<tr>
<td style="width: 175px;">&nbsp;&nbsp;&nbsp;<b>Horizontal Spacing</b></td>
<td><input type="text" maxlength="3" style="width: 65px; font-size: 16px;" name="appreplicaicons-other-spacing-h" value="<?php echo get_site_option('appreplicaicons-other-spacing-h', 3); ?>"  placeholder="" />&nbsp; <i style="color: #999;">A number between 0 and 25</i></td>
</tr>

<tr>
<td style="width: 175px;">&nbsp;&nbsp;&nbsp;<b>Vertical Spacing</b></td>
<td><input type="text" maxlength="3" style="width: 65px; font-size: 16px;" name="appreplicaicons-other-spacing-v" value="<?php echo get_site_option('appreplicaicons-other-spacing-v', 3); ?>"  placeholder="" />&nbsp; <i style="color: #999;">A number between 0 and 25</i></td>
</tr>

<tr>
<td>&nbsp;&nbsp;&nbsp;<b>Color</b></td>
<td><input type="text" name="appreplicaicons-other-color" value="<?php echo get_site_option('appreplicaicons-other-color', '#666666'); ?>" class="appreplica-color-picker" /></td>
</tr>

<tr>
<td>&nbsp;&nbsp;&nbsp;<b>Color on Hover</b></td>
<td><input type="text" name="appreplicaicons-other-color-hover" value="<?php echo get_site_option('appreplicaicons-other-color-hover', '#999999'); ?>" class="appreplica-color-picker" /></td>
</tr>

<tr>
<td>&nbsp;&nbsp;&nbsp;<b>Preview</b></td>
<td>

<style>
.appreplicaicons-other-preview { 
	font-size: <?php echo get_site_option('appreplicaicons-other-size', 24); ?>px;
	color: <?php echo get_site_option('appreplicaicons-other-color'); ?>; 
	margin-left: <?php echo get_site_option('appreplicaicons-other-spacing-h', 3); ?>px; 
	margin-right: <?php echo get_site_option('appreplicaicons-other-spacing-h', 3); ?>px;
	margin-top: <?php echo get_site_option('appreplicaicons-other-spacing-v', 3); ?>px; 
	margin-bottom: <?php echo get_site_option('appreplicaicons-other-spacing-v', 3); ?>px; 
	text-decoration: none;
}
a.appreplicaicons-other-preview:link { color: <?php echo get_site_option('appreplicaicons-other-color'); ?>; text-decoration: none;}
a.appreplicaicons-other-preview:visited { color: <?php echo get_site_option('appreplicaicons-other-color'); ?>; }
a.appreplicaicons-other-preview:hover { color: <?php echo get_site_option('appreplicaicons-other-color-hover'); ?>; }
</style>

<?php 
include ("appreplica-social-icons-list.php");
foreach ($appreplicaicons as $value) {
$varName = 'appreplicaicons_icon_' . $value[0] . '_' . $value[1];
if (get_site_option($varName) == 1) {
if ($value[1] == 99) {
?>
<a href="javascript:" class="appreplicaicons-other-preview"><i class="fa fa-<?php echo $value[0]; ?> fa-fw"></i></a>
<?php } } } ?>

</td>
</tr>

<tr><td colspan="2"><?php submit_button(); ?></td></tr>

</table>

</form>

<script type="text/javascript">
jQuery(document).ready(function($){
    $('.appreplica-color-picker').wpColorPicker();
});
</script>

<br /><br /><br />
<hr style="border: none; border-bottom: 1px solid #ccc;" />

<?php } // End Style Tab ?>




<?php if( $appreplicaicons_active_tab == 'support' ) { // Support Tab ?>

<table class="form-table">
<tbody>

<tr valign="top">
<td>

<div style="max-width: 1100px; font-size: 14px;">

<h3>How do I get started?</h3>

<ol style="font-size: 15px;">
<li>From the <b>Icons</b> tab, select / deselect the predefined icons you wish to include in your widgets.</li>
<li>Use to the <b>Uploads</b> tab to upload your own custom icons.</li>
<li>Use to the <b>Configure</b> tab to specify the URL and name for each of your selected icons.</li>
<li>Use the <b>Size / Color</b> tab to specify your own custom icon sizes and colors.</li>
<li>Drag the <b>Appreplica Social Icons</b> widget from the <b>Appearance &gt; Widgets</b> section to your desired widget locations.</li>
<li>Alternatively you may use the shortcode <code>[appreplicasocialicons][/appreplicasocialicons]</code> with the built in Text widget and in Pages/Posts.</li>
</ol>

<br />

<h3>The shortcode doesn't work when I add it to the Text Widget?</h3>

Some themes are setup by default to not parse the text widget for shortcodes, resulting in only the shortcode text to appear instead of the icons. To fix this, you must enable shortcodes for the text widget by adding the following code to your theme's <b>function.php</b> file. In most cases, you can simply use the Appearance&nbsp;&gt;&nbsp;Editor option to add this line without having to resort to FTP or other means of file access.<br /><br />

<code>add_filter('widget_text', 'do_shortcode');</code>

<br /><br />

<h3>What's the purpose of this plugin?</h3>

This free plugin lets you add custom icons for major social and media content websites to the widgets sections of your WordPress powered websites. It's designed primarily for the users of our <a target="_blank" href="https://wordpress.org/plugins/appreplica/"><b>Appreplica</b></a> Plugin in order to create icons in their widget sections linking to their embedded content pages, but it can also be used by others. Each icon can be linked to any relative or external URL of your choosing, and you can customize both the size and its colors.<br /><br />

</div>
</td>
</tr>

</tbody>
</table>

<br /><br /><br />
<hr style="border: none; border-bottom: 1px solid #ccc;" />

<?php } // End Support Tab ?>




</div>
<?php } ?>