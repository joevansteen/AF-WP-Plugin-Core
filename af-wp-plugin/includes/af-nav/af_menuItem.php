<?php
/*
 * af_menuItem script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-28 JVS Original code
 *
 * Inspired by the menu class in The PHP Anthology, Object Oriented PHP
 * Solutions, Volume 1, by Harry Fuecks, Copyright 2003 Sitepoint Pty Ltd
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_MenuItem';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_MenuItem
	 *
	 * Defines an encapsulation of a menu item. Menu items define the detail
	 * information in support of menu elements.
	 ***************************************************************************/
class C_MenuItem extends C_AirObjectBase {
//	private $itemKey;
//	private $itemLabel;
//	private $itemDescription;
//	private $itemTarget;
//	private $itemAction;

	/***************************************************************************
	 * Constructor
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

	/***************************************************************************
	 * initialize()
	 *******/
//	function initialize($itemKey, $itemLabel, $itemDescription,
// 								$itemTarget, $itemAction)
//		{
//		$this->itemKey				= $itemKey;
//		$this->itemLabel			= $itemLabel;
//		$this->itemDescription	= $itemDescription;
//		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
	 	{
		parent::terminate();
		}

	} // end of class
?>