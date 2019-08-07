<?php

/**
 The Architected Futures WordPress Plugin bootstrap file
 
 One file. The same file. For everyone. For everything. Open. With a traceable
 audit trail that can run for as long as someone wants to pay for the associated
 physical storage that it takes to maintain their own components in the mesh of
 cyber-phys interaction and information flow which makes up the audit trail.
 
    For those who also want to pay for a degree of certitude to authenticate
    replication of derives of AF information over time using blockchain type
    certification -- it would be helpful to pre-calculate an estimate of what
    your compute costs will be to achieve your end, which will always be citing
    a certitude about a historical relic at what value of significance? That 
    becomes the issue for cyber currency - how much do you have to pay to buy
    trust, for how long; and what is it worth right now, and where is its relative
    value going over time? One human will always be equal in value to one human.
    There is no inflation. We do argue about caste levels and such relative to
    estimated evolutionary geneology, and familial cousins, and inter-breeding
    with unpure stock along various points and times in history.
    
    This is true for aspects of Anthropology, and others in the models, and it
    needs recognitions by the modelers. Especially forecasters and planners.
    It is a bias, which is considered benevolent, which is built into the design
    structure of the EATSv5 system as an axiom.
    
    Most people don't really care. They are here now, and moving toward tonight,
    tomorrow, and next week. They are concerned a little about next year, and 
    that is a matter of condition and circumstances. Potentially children, or
    grand children lives. If young, when I grow up. Toward later working life,
    when I/we retire.
    
    All EATSv5 implementations are owned by somebody. A person. All interactions
    with an EATSv5 facility are considered to be direct interactions with the
    site owner on a one:one basis with EATSv5 taking the role of the colon in the
    equation
    
    					ONE (you the user) : (EATSv5 as interface) ONE (the site owner)
    			
    			           ALWAYS                                     ALWAYS		
    					       OTHER           <= pro / con ? =>        ME / WE / US
 					       (ContraParty)
    					
    					 Distinctive Party <= Channel Multiplexing => "Rational?" Self
    					Interface Operator <= interaction mechnism => Interfaced Party
    					
    					==============================================================
    
     This is THE STANDARD design for anyone paying for the development of software
     to be used by other, for the ultimate benefit of the owner of the software
     capability. It is a standard balance sheet model that works for goods in the
     material world but does not function when shifted to a world where the economics
     of human interactions needs to be based on the co-equal value of all human life,
     not on the basis of how high a debt in future human misery one can pile up before
     one takes responsible action for current decisions and true physical debts, not
     abstract, conceptual, monetary debt, which will have to be paid by future 
     generations. EATSv5 is a record keeping system, with a balance sheet.
     
     This can get deep, but it can get fun in a lot of directions. Or, it can be calm
     and shallow. That is the intent.
     
     Calm and Shallow: Stop reading now. Use the system. Go. Seriously, Bye. You do
     not need to read more unless you are a real nerd about some of this. A geek of
     a historical level.
     
     If you've already read some of the other docs, read on ...
 
 For most record systems there is an opening transaction, and, eventually, a closing
 transaction. This is defined by the Matt Flavin metadata elements in the AIR metamodel.
 After that, it's a matter of who wants to pay to hang on to how much of what for how
 long. Business economics of data storage, versus value; encumbered by policy demands,
 business direction, mission, principles, vision, values, ... etc. That's cyber-phys.
 All cyber memory is carved with physics and deteriorates, or disappears, unless
 maintained. Nothing lasts forever, for free. So, it's a question of what's it
 worrth to maintain, for how long; versus what are the consequences of having it lost,
 possibly forever? That involves risk assessment and analysis, conclusions, and decisions.
 
 I started that analysis in 1970. After ACME Candle. When the real world of CSK identified
 what identi-cards had evolved to. TAB card computing, record keeping, and accounting
 systems in the first wave of automation. Garbage in date and dollar amount fields, due to
 keypunch errors, which were not caught by the wired logic boards that sorted and merged
 tab cards to produce printed reports of results where letters and numbers both
 qualified numerical opersands.
 
 
 That was also a step on the gradual path we call the growth and evolution of artificial
 intelligence. Well, my artificial intelligence in s/360 COBOL code was way, way better
 than the AI in the logic boards and the system that was previously keeping the books.
 And, that is the story of a lot of cyber. Architected Futures is just my version of
 what I think a lot of folks, me atleast with my CDOS Cromemco, were looking forward to
 possibly coming out of the ALTAIR, the Altos, the Osborne, CP/M and the First Computer
 Faire.
 
 This file is read by WordPress to assimilate knowledge about the plugin and to
 propogate the plugin information in the plugin admin area for the associated website,
 and the related operational control aspects of WordPress operations.
  
 The file also includes top level dependencies used by the plugin, it registers the
 activation and deactivation functions with WP, and defines a function that allows
 WP to start the plugin.
 
 @link              https://architectedfutures.org
 @since             5.2019.0805
 @package           AF_WP_Plugin
 
 @wordpress-plugin
 Plugin Name:       AF WP Plugin Instance
 Plugin URI:        https://jvs.guru/eatsv5/af-wp/
 Description:       AF EATSv5 WP API, architected instance, and federated interaction gateway.
 Version:           5.2019.0805
 Author:            Joe Van Steen
 Author URI:        https://jvs.guru/
 License:           GPL-3.0+
 License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 Text Domain:       af-wp-plugin
 Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 5.2019.0805 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AF_WP_PLUGIN_VERSION', '5.2019.0805' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-af-wp-plugin-activator.php
 */
function activate_af_wp_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-af-wp-plugin-activator.php';
	AF_WP_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-af-wp-plugin-deactivator.php
 */
function deactivate_af_wp_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-af-wp-plugin-deactivator.php';
	AF_WP_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_af_wp_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_af_wp_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-af-wp-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    5.2019.0805
 */
function run_af_wp_plugin() {

	$af_wp_plugin = new AF_WP_Plugin();
	$af_wp_plugin->run();

}
run_af_wp_plugin();
/**
 * Close the module properly!
 */
 ?>