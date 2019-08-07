<?php
/*
 * af_dialog_pac_root script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-19 JVS Original Version
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_DialogPAC_Root';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

/*****************************************************************************
 * C_AF_DialogPAC_Root
 * This class defines the functional base class for an AF Dialog PAC object.
 * It provides foundation base class data defining the state and functionality
 * of the Dialog PAC hierarchy.
 *********/
class C_AF_DialogPAC_Root extends C_AirObjectBase {
	private $dispatcher;				// The message loop dispatcher object

	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local reference to the
	 * AIR_anchor object for later use in detail function processing. (Be careful
	 * with code here to ensure that we are really talking to the right object.)
	 *******/
	function __construct(&$air_anchor)
		{
		// Propogate the construction process
		parent::__construct($air_anchor);

		if ($air_anchor->trace())
			{
			$air_anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		}

	} // End of class C_AF_DialogPAC_Root
?>