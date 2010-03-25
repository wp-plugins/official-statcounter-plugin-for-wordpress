<?php
/*
 * Plugin Name: Official StatCounter Plugin
 * Version: 1.3
 * Plugin URI: http://www.statcounter.com/
 * Description: Adds the StatCounter tracking code to your blog. After uploading this plugin click 'Activate' (to the right) and then afterwards you must visit the <a href="options-general.php?page=StatCounter-Wordpress-Plugin.php">options page</a> and enter your StatCounter Project Info to enable logging.
 * Author: Aodhan Cullen
 * Author URI: http://www.statcounter.com/
 */

// Constants for enabled/disabled state
define("sc_enabled" , "enabled", true);
define("sc_disabled" , "disabled", true);

// Defaults, etc.
define("key_sc_project", "sc_project", true);
define("key_sc_part", "sc_part", true);
define("key_sc_status", "sc_status", true);
define("key_sc_position", "sc_position", true);
// legacy problem with sc_security naming
define("key_sc_security", "key_sc_security", true);



define("sc_project_default", "0" , true);
define("sc_part_default", "0" , true);
define("sc_security_default", "" , true);
define("sc_status_default", sc_disabled , true);
define("sc_position_default", "footer", true);
define("sc_admin_default", sc_enabled , true);

// Create the default key and status
add_option(key_sc_status, sc_status_default, 'If StatCounter logging in turned on or off.');
add_option(key_sc_project, sc_project_default, 'Your StatCounter Project ID.');
add_option(key_sc_part, sc_part_default, 'Your StatCounter Partition ID.');
add_option(key_sc_security, sc_security_default, 'Your StatCounter Security String.');
add_option("sc_invisible", "0", 'Force invisibility.');

// Create a option page for settings
add_action('admin_menu' , 'add_sc_option_page' );
add_action( 'admin_menu', 'statcounter_admin_menu' );

function statcounter_admin_menu() {
	$hook = add_submenu_page('index.php', __('StatCounter Stats'), __('StatCounter Stats'), 'publish_posts', 'statcounter', 'statcounter_reports_page');
	add_action("load-$hook", 'statcounter_reports_load');
	$hook = add_submenu_page('plugins.php', __('StatCounter Admin'), __('StatCounter Admin'), 'manage_options', 'statcounter_admin', 'sc_options_page');
}

function statcounter_reports_load() {
	add_action('admin_head', 'statcounter_reports_head');
}

function statcounter_reports_head() {
?>
<style type="text/css">
	body { height: 100%; }
</style>
<?php
}

function statcounter_reports_page() {
	$sc_project = get_option(key_sc_project);
	
	
	echo '<iframe id="statcounter_frame" src="http://my.statcounter.com/project/standard/stats.php?project_id='.$sc_project.'" width="100%" height="2000">
<p>Your browser does not support iframes.</p>
</iframe>';

}



// Hook in the options page function
function add_sc_option_page() {
	global $wpdb;
	add_options_page('StatCounter Options', 'StatCounter', 8, basename(__FILE__), 'sc_options_page');
}

function sc_options_page() {
	// If we are a postback, store the options
 	if ( isset( $_POST['info_update'] ) ) {
		check_admin_referer();
		
		// Update the status
		$sc_status = $_POST[key_sc_status];
		if (($sc_status != sc_enabled) && ($sc_status != sc_disabled))
			$sc_status = sc_status_default;
		update_option(key_sc_status, $sc_status);

		// Update the Project ID
		$sc_project = $_POST[key_sc_project];
		if ($sc_project == '')
			$sc_project = sc_project_default;
		update_option(key_sc_project, $sc_project);

		// Update the part ID
		$sc_part = $_POST[key_sc_part];
		if ($sc_part == '')
			$sc_part = sc_part_default;
		update_option(key_sc_part, $sc_part);

		// Update the Security ID
		$sc_security = $_POST[key_sc_security];
		if ($sc_security =='')
			$sc_security = sc_security_default;
		update_option(key_sc_security, $sc_security);
		
		// Update the position
		$sc_position = $_POST[key_sc_position];
		if (($sc_position != sc_header) && ($sc_position != sc_footer))
			$sc_status = sc_position_default;
		update_option(key_sc_position, $sc_position);
		
		// Force invisibility
		$sc_invisible = $_POST['sc_invisible'];
		if ($sc_invisible == 1) {
			update_option('sc_invisible', "1");		
		} else {
			update_option('sc_invisible', "0");				
		}

		// Give an updated message
		echo "<div class='updated'><p><strong>StatCounter options updated</strong></p></div>";
	}

	// Output the options page
	?>

		<form method="post" action="options-general.php?page=StatCounter-Wordpress-Plugin.php">
		<div class="wrap">
			<?php if ( get_option( key_sc_status ) == sc_disabled ) { ?>
				<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
				StatCounter Wordpress Plugin is currently <strong>DISABLED</strong>.
				</div>
			<?php } ?>
			<?php if ( ( get_option( key_sc_project ) == "0" ) && ( get_option( key_sc_status ) != sc_disabled ) ) { ?>
				<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
				StatCounter Plugin is currently enabled, but the following errors are noted:<ul style="padding:0;margin:0;"><?php
					echo ( get_option( key_sc_project ) == "0" ? "<li>No <strong>Project ID</strong> has been provided</li>" : "" );
				?></ul><strong>Tracking will not occur</strong>.
				</div>
			<?php } ?>
			<h2>Using StatCounter</h2>
			<blockquote><a href="http://www.statcounter.com" style="font-weight:bold;">StatCounter</a> is a free web traffic analysis service, which provides summary stats on all your traffic and a detailed analysis of your last 500 page views. This limit can be increased by subscribing to their paid service.</p>
			<p>To activate the StatCounter service for your WordPress site:<ol>
				<li>Register/Login to <a href="http://www.statcounter.com" style="font-weight:bold;">StatCounter</a></li>
				<li>Select <a href="http://my3.statcounter.com/project/add.php" style="font-weight:bold;">Add New Project</a> from your Projects Menu</li>
				<li>Complete the requested details regarding your site, then click "Next"</li>
				<li>Click the "<strong>Configure & Install Code</strong>" button</li>
				<li>Select and configure the type of counter your would like</li>
				<li>Select "<strong>Wordpress.org (I pay for the hosting)</strong>" from the drop down list, then click "Next"</li>
				<li>From the generated StatCounter Code, copy the bolded sections:<br />
					'<em>var sc_project=</em><strong>1234567</strong>' - Your Project ID<br />
					'<em>var sc_partition=</em><strong>12</strong>' - Your Partition Number<br />
					'<em>var sc_security="</em><strong>a1b2c3d4</strong><em>"</em>' - Your Security Code (Don't grab the inverted commas)</li>
				<li>Enter those details into the relevant fields below</li>
				<li>Click "Update Options"</li>
			</ol></blockquote>
			<h2>StatCounter Options</h2>
			<blockquote>
			<fieldset class='options'>
				<table class="editform" cellspacing="2" cellpadding="5">
					<tr>
						<td>
						Logging:
						</td>
						<td>
							<?php
							echo "<select name='".key_sc_status."' id='".key_sc_status."'>\n";
							
							echo "<option value='".sc_enabled."'";
							if(get_option(key_sc_status) == sc_enabled)
								echo " selected='selected'";
							echo ">Enabled</option>\n";
							
							echo "<option value='".sc_disabled."'";
							if(get_option(key_sc_status) == sc_disabled)
								echo" selected='selected'";
							echo ">Disabled</option>\n";
							
							echo "</select>\n";
							?>
						</td>
					</tr>
					<tr>
						<td>
							Project ID:
						</td>
						<td>
							<?php
							echo "<input type='text' size='11' ";
							echo "name='".key_sc_project."' ";
							echo "id='".key_sc_project."' ";
							echo "value='".get_option(key_sc_project)."' />\n";
							?>
						</td>
					</tr>
					<tr>
						<td>
						Partition Number:
						</td>
						<td>
							<?php
							echo "<input type='text' size='3' ";
							echo "name='".key_sc_part."' ";
							echo "id='".key_sc_part."' ";
							echo "value='".get_option(key_sc_part)."' />\n";
							?>

						</td>
					</tr>
					<tr>
						<td>
						Security Code:
						</td>
						<td>
							<?php
							echo "<input type='text' size='9' ";
							echo "name='".key_sc_security."' ";
							echo "id='".key_sc_security."' ";
							echo "value='".get_option(key_sc_security)."' />\n";
							?>
						</td>
					</tr>
					<tr>
						<td>
						Counter Position:
						</td>
						<td>
							<?php
							echo "<select name='".key_sc_position."' id='".key_sc_position."'>\n";
							
							echo "<option value='header'";
							if(get_option(key_sc_position) == "header")
								echo " selected='selected'";
							echo ">Header</option>\n";
							
							echo "<option value='footer'";
							if(get_option(key_sc_position) != "header")
								echo" selected='selected'";
							echo ">Footer</option>\n";
							
							echo "</select>\n";
							?>
						</td>
					</tr>
					<tr>
						<td>
						Force invisibilty:
						</td>
						<td>
							<?php
							$checked = "";
							if(get_option('sc_invisible')==1) {
								$checked = "checked";
							}
							
							echo "<input type='checkbox' name='sc_invisible' id='sc_invisible' value='1' ".$checked.">\n";
							?>
						</td>
					</tr>								
				</table>
			</fieldset>
			</blockquote>
						<p class="submit">
				<input type='submit' name='info_update' value='Update Options' />
			</p>
		</div>
		</form>

<?php
}
//print_r($_GET);
//print_r($_POST);
//die();
//echo $sc_position;
$sc_position = get_option(key_sc_position);
//die($sc_position);
if ($sc_position=="header") {
	add_action('wp_head', 'add_statcounter');
} else {
	add_action('wp_footer', 'add_statcounter');
}





// The guts of the StatCounter script
function add_statcounter() {
	global $user_level;
	$sc_project = get_option(key_sc_project);
	$sc_part = get_option(key_sc_part);
	$sc_security = get_option(key_sc_security);
	if (
		( get_option( key_sc_status ) != sc_disabled && $sc_project > 0 )
	 ) {
?>
	<!-- Start of StatCounter Code -->
	<script type="text/javascript">
	<!-- 
		var sc_project=<?php echo $sc_project; ?>; 
		var sc_partition=<?php echo $sc_part; ?>; 
		var sc_security="<?php echo $sc_security; ?>"; 
<?php 
if(get_option('sc_invisible')==1) {
	echo "		var sc_invisible=1;\n"; 
}?>
	//-->
	</script>
	<script type="text/javascript" src="http://www.statcounter.com/counter/counter_xhtml.js"></script>
	<!-- End of StatCounter Code -->
<?php
	}
}

?>