<?php
/*
 * C_AF_BpProfileSync script Copyright (c) 2011 Joe Van Steen
 *
 * Defines a BuddyPress component to provide profile synchronization between
 * BuddyPress and WordPress.
 *
 * @package AF Core
 * @subpackage AF BP Profile Synchronization
 * @since 02.03.01
 *
 * V1.0 2011-AUG-25 JVS Original development
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_BP_ProfileSync';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_BP_ProfileSync
	 *
	 * Defines a BuddyPress component to provide profile synchronization between
	 * BuddyPress and WordPress.
	 *
	 * This component is built leveraging the abstract base BP_Component which was
	 * introduced as part of BP v1.5. That component provides a stabndard base
	 * class for selected initialization and interface standards concerning the
	 * appropriate method for performing these actions with the BuddyPress core.
	 *
	 * see: http://buddypress.org/community/groups/creating-extending/forum/topic/jjj-what-is-this-you-reference-in-your-word-camp-video
	 ***************************************************************************/
class C_AF_BP_ProfileSync extends BP_Component {

	function __construct(){
		parent::start(
		'example',
		__('Example', 'buddypress'),
		BP_PLUGIN_DIR
		);
	}

	function includes() {
		$includes = array(
		'actions',
		'screens',
		'template',
		'functions',
		);
	parent::includes($includes);
	}
} // End of class C_AF_BP_ProfileSync

?>