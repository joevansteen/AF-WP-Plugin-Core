<?php
/*
 * dlg_GenRegCode.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-NOV-07 JVS Original code
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirGenRegCodeImage';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirGenRegCodeImage extends C_HtmlImage {
	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing. (Be careful with code here
	 * to ensure that we are really talking to the right object.)
	 *******/
	function __construct()
		{
		// Propogate the construction process
		parent::__construct();

		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		}

	/***************************************************************************
	 * initialize
	 *
	 * Initialize at this level consists of 'building' the result panel as a
	 * series of object specifications in the panel shell.
	 *******/
	function initialize($panelClass = '')
	 	{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		parent::initialize($panelClass);
		$this->imageObject = new C_AirSecurityPrompt();
		$this->imageObject->initialize(AF_ROOT_DIR.'/images/regCodeMask.jpg');
//		$this->imageObject->initialize(AF_ROOT_DIR.'/images/cooltext46666849.jpg');
		$securityCode	= $this->imageObject->createRandomString();
		$this->imageObject->addText($securityCode);
		$this->anchor->sessionDoc->putSessionData('dlgSecurityCode', $securityCode);
		$this->contentType = 'image/jpeg';
		}

	} // end of class
/*******************************************************************
 *******************************************************************/

 ?>