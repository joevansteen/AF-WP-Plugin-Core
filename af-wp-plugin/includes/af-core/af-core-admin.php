<?php
/**
 * Administrative Functions File
 *
 * This file provides the AF Core functionality associated with WordPress Dashboard functions
 *
 * @package AF Core
 * @subpackage Admin Functions
 * @since 02.03.01
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myFeatureSet = 'AF-CORE API';
$myProcClass = 'AF_Core_Admin';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class AF_Core_Admin {
	/**
	 * PHP 4 constructor
	 *
	 * @package AF Core
	 * @since 02.03.01
	 */
	function af_core_admin() {
		$this->__construct();
	}

	/**
	 * PHP 5 constructor
	 *
	 * @package AF Core
	 * @since 02.03.01
	 */
	function __construct() {
		/* Add action to be run at the beginning of every admin page before rendering panel */
		add_action('admin_init', array($this, 'register_af_core_options' ));
		/* Add action to be run after the basic admin panel menu structure is in place */
		add_action('admin_menu', array($this, 'af_core_add_pages' ));
		/* Register the activation hook */
		register_activation_hook(__FILE__, array($this, 'af_core_activation' ));
		/* Register the deactivation hook */
		register_deactivation_hook(__FILE__, array($this, 'af_core_deactivation' ));

		/* Add plugin action links to appear below th eplugin name in the WP plugins menu */
		add_filter('plugin_action_links', array($this, 'af_core_plugin_actions'), 10, 2 );
	}

		/***********
		 * Add plugin action links to appear below th eplugin name in the WP plugins menu
		 * Could also be used to add a 'donate' link.
		 **********/
	function af_core_plugin_actions($links, $file) {
	 	if ($file == 'af-core/af-core.php' && function_exists("admin_url")) {
			$settings_link = '<a href="' . admin_url('options-general.php?page=af-core') . '">' . __('Settings', 'af-txt-domain') . '</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}

	function register_af_core_options(){
		/*********
		 * Register a setting
		 *
		 *  option group: af_core
		 *  option_name: af_core
		 ********/
		register_setting( 'af_core', 'af_core' );
	}

	function af_core_add_pages() {
	    /*******
   	  * Add a new submenu under Options:
	     *   Page title: AF Core
   	  *   Menu title: AF Core
	     *   Capability required: manage_options
   	  *   Menu slug: af_core
	     *   function: af_core_options
   	  *******/
		$css = add_options_page('AF Core', 'AF Core', 'manage_options', 'af-core', array($this, 'af_core_options'));
		/* Add action to be run in the HTML <head> section of our admin page / panel for our plugin */
		add_action("admin_head-$css", array($this, 'af_core_css'));
	}

	function af_core_css() { ?>
		<style type="text/css">
		#af-core, #parent-page, #previous-page { float: left; width: 30%; margin-right: 5%; }
		#af-core { margin-right: 0; }
		</style>
		<?php
		}

	/********************************************************************************************
	 * OPTIONS PAGE
	 *
	 * displays the options page content
	 ********************************************************************************************/
	function af_core_options() { ?>
	    <div class="wrap">
		<form method="post" id="af_core_form" action="options.php">
			<?php settings_fields('af_core');
			$options = get_site_option('af_core'); ?>

	    <h2><?php _e( 'AF Core Options', 'af-txt-domain'); ?></h2>

		<p><?php _e("On the first and last pages in the sequence:", 'af-txt-domain'); ?><br />
   	 <label><input type="radio" name="af_core[loop]" id="loop" value="1" <?php checked('1', $options['loop']); ?> />
			<?php _e("Loop around, showing links back to the beginning or end", 'af-txt-domain'); ?></label><br />
		<label><input type="radio" name="af_core[loop]" id="loop" value="0" <?php checked('0', $options['loop']); ?> />
			<?php _e("Omit the empty link", 'af-txt-domain'); ?></label>
		</p>

	    <p><label><?php _e("Exclude pages: ", 'af-txt-domain'); ?><br />
	    <input type="text" name="af_core[exclude]" id="exclude"
			value="<?php echo $options['exclude']; ?>" /><br />
		<small><?php _e("Enter page IDs separated by commas.", 'af-txt-domain'); ?></small></label></p>

   	 <div id="previous-page">
	    <h3><?php _e("Previous Page Display:", 'af-txt-domain'); ?></h3>
   	 <p><label><?php _e("Before previous page link: ", 'af-txt-domain'); ?><br />
	    <input type="text" name="af_core[before_prev_link]" id="before_prev_link"
			value="<?php echo esc_html($options['before_prev_link']); ?>" />  </label></p>

	    <p><label><?php _e("Previous page link text: <small>Use %title% for the page title</small>", 'af-txt-domain'); ?><br />
   	 <input type="text" name="af_core[prev_link_text]" id="prev_link_text"
			value="<?php echo esc_html($options['prev_link_text']); ?>" />  </label></p>

	    <p><label><?php _e("After previous page link: ", 'af-txt-domain'); ?><br />
   	 <input type="text" name="af_core[after_prev_link]" id="after_prev_link"
			value="<?php echo esc_html($options['after_prev_link']); ?>" />  </label></p>
	    <p><?php _e('Shortcode:'); ?> <strong>[af-previous]</strong><br />
   	 <?php _e('Template tag:'); ?> <strong>&lt;?php af_previous_link(); ?&gt;</strong></p>
	    </div>

	    <div id="parent-page">
   	 <h3><?php _e("Parent Page Display:", 'af-txt-domain'); ?></h3>
	    <p><label><?php _e("Before parent page link: ", 'af-txt-domain'); ?><br />
   	 <input type="text" name="af_core[before_parent_link]" id="before_parent_link"
			value="<?php echo esc_html($options['before_parent_link']); ?>" />  </label></p>

	    <p><label><?php _e("Parent page link text: <small>Use %title% for the page title</small>", 'af-txt-domain'); ?><br />
   	 <input type="text" name="af_core[parent_link_text]" id="parent_link_text"
			value="<?php echo esc_html($options['parent_link_text']); ?>" />  </label></p>

	    <p><label><?php _e("After parent page link: ", 'af-txt-domain'); ?><br />
   	 <input type="text" name="af_core[after_parent_link]" id="after_parent_link"
			value="<?php echo esc_html($options['after_parent_link']); ?>" />  </label></p>
	    <p><?php _e('Shortcode:'); ?> <strong>[af-parent]</strong><br />
   	 <?php _e('Template tag:'); ?> <strong>&lt;?php parent_link(); ?&gt;</strong></p>
	    </div>

	    <div id="af-core">
   	 <h3><?php _e("Next Page Display:", 'af-txt-domain'); ?></h3>
	    <p><label><?php _e("Before next page link: ", 'af-txt-domain'); ?><br />
   	 <input type="text" name="af_core[before_next_link]" id="before_next_link"
			value="<?php echo esc_html($options['before_next_link']); ?>" />  </label></p>

	    <p><label><?php _e("Next page link text: <small>Use %title% for the page title</small>", 'af-txt-domain'); ?><br />
   	 <input type="text" name="af_core[next_link_text]" id="next_link_text"
			value="<?php echo esc_html($options['next_link_text']); ?>" />  </label></p>

	    <p><label><?php _e("After next page link: ", 'af-txt-domain'); ?><br />
   	 <input type="text" name="af_core[after_next_link]" id="after_next_link"
			value="<?php echo esc_html($options['after_next_link']); ?>" />  </label></p>
	    <p><?php _e('Shortcode:'); ?> <strong>[af-next]</strong><br />
	    <?php _e('Template tag:'); ?> <strong>&lt;?php af_next_link(); ?&gt;</strong></p>
   	 </div>

		<p class="submit">
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Update Options', 'af-txt-domain'); ?>" />
		</p>
		</form>
		</div>
	<?php
	} // end function af_core_options()

	// When activated, set up options
	function af_core_activation() {
		// Ensure a proper WP version, else deactivate
		if ( version_compare( get_bloginfo('version'), '3.1', '<' )) {
			deactivate_plugins('af_core');
			return;
		}

		/* Register the uninstall hook - cannot be a class method (according to WROX Pro WordPress Plugin Development
		 * This information is stored in the database and does not need to be repeated
		 * each time the plugin is loaded, once at activation is enough. The DB store of
		 * the data is the rationale for not using class methods, since this class may
		 * be long gone. (Class methods may/should work if this is registered every time
		 * the module is loaded. However, that causes extra DB IO to record the option.
		 * Neither way is great. Another option is to move the function to a separate uninstall.php file.
		 */
		register_uninstall_hook( __FILE__, 'af_core_delete_options');

		// set defaults
		$options = array();
		$options['af_core_version'] = AF_CORE_VERSION;
		$options['before_prev_link'] = '<div class="alignleft">';
		$options['prev_link_text'] = __('&#8592;', 'af-txt-domain').' %title%';      /*  Left Arrow &#8592; */
		$options['after_prev_link'] = '</div>';

		$options['before_parent_link'] = '<div class="aligncenter">';
		$options['parent_link_text'] = __('&#8593;', 'af-txt-domain').' %title%';  /* Up Arrow  &#8593; */
		$options['after_parent_link'] = '</div>';

		$options['before_next_link'] = '<div class="alignright">';
		$options['next_link_text'] = '%title% '.__('&#8594;', 'af-txt-domain');     /*   Rt Arrow &#8594;  */
		$options['after_next_link'] = '</div>';

		$options['exclude'] = '';
		$options['loop'] = 0;

		// set new option
		add_option('af_core', array_merge($oldoptions, $options), '', 'yes');
	}

	// When deactivated, nothing to do right now
	function af_core_deactivation() {
		/*
		 * Leave the options in place, in case they were changed and may be wanted
		 * the next time the module is activated.
		 */
		return;
	}

}

// when uninstalled, remove option
function af_core_delete_options() {
	delete_option('af_core');
}

?>