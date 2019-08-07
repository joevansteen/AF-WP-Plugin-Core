<?php
/*
 * dlg_Login.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-16 JVS Begin test of new standalone PHP environment script
 * V1.3 2005-NOV-01 JVS Integration with old Tikiwiki AIR base
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirLoginPanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirLoginPanel extends C_HtmlPanel {
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

		/*
		 * Get out data variables for use in configuring the detail portions
		 * of the result panel
		 */
		$actionRefPrefix	= $this->anchor->getActionUrlBase().'dialog='.$this->getPanelName();
		$textareaWidth 	= $this->stdTextareaWidth;

	  	$userName = $this->anchor->getDlgVar('user');
	  	$userPswd = $this->anchor->getDlgVar('pswd');

//		$dlgPanelType				= $this->anchor->getDlgVar('dlgPanelType');
		$air_showDiagnostics		= $this->anchor->getDlgVar('air_showDiagnostics');
		$resultDiag					= $this->anchor->getDlgVar('resultDiag');

//		$panelItemTitle			= $this->anchor->getDlgVar('panelItemTitle');
//		$panelItemSubtitle		= $this->anchor->getDlgVar('panelItemSubtitle');
//		$air_ItemHeader			= $this->anchor->getDlgVar('air_ItemHeader');
//		$air_ItemFooter			= $this->anchor->getDlgVar('air_ItemFooter');
//		$air_Dialog					= $this->anchor->getDlgVar('air_Dialog');

		/*
		 * Construct and file our personality objects into the panel outline
		 */
//		$this->setHiddenElement('dlgContext',		$this->anchor->sessionDoc->getContextId());
//		$this->setHiddenElement('dlgPanelType',	$dlgPanelType);
//		$this->setHiddenElement('dlgClientDlgID',	'Client2randomvalue');
//		$this->setHiddenElement('dlgCorrID',		$this->anchor->sessionDoc->getCorrId());
//		$this->setHiddenElement('dlgDlgID',			$this->anchor->sessionDoc->getDialogId());
//		$this->setHiddenElement('dlgMode',			$this->anchor->sessionDoc->getModeId());
//		$this->setHiddenElement('dlgAuth',			$this->anchor->sessionDoc->getAuthId());

		$this->pageTitle = 'Login';

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleH1, null, 'Member Login');
		$this->panelTitle = & $myTitleData;

		$itemArray	= array();
		$pageItem	= array();

		if ($air_showDiagnostics)
			{
			$itemMessages	= array();
			foreach ($resultDiag as $diagItem)
				{
				$itemMessage	=	array();
				$itemMessage['msgItem']	= 	$diagItem['msgItem'];
				$itemMessage['msgText']	=	$diagItem['msgText'];
				if (array_key_exists('msgType', $diagItem))
					{
					$itemMessage['msgType']	= 	$diagItem['msgType'];
					}
				$itemMessages[]	= $itemMessage;
				}
			$pageItem['itemMessages']	= $itemMessages;
			}

//		$pageItem['itemTitle']	= & $myTitleData;

//		$pageItem['itemHeader']	= & $this->formatStdDialogArray($air_ItemHeader);

		$myTable = new C_AirHtmlElement($this);
		$myTable->initialize(htmlEleTable);

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, 'Registered users');
							 $myTabData->setAttribute(htmlAttrClass, 'LinkPrompt');
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->createChildSiteLink('Not registered?', AIR_Action_New, null, 'LinkPrompt');
							 $myTabData->setAttribute(htmlAttrAlign, 'right');

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, 'please log in ...');
							 $myTabData->setAttribute(htmlAttrClass, 'LinkPrompt');
							 $myTabData->setAttribute(htmlAttrAlign, 'left');
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, '&#160;');
							 $myTabData->setAttribute(htmlAttrAlign, 'right');

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, 'User ID (Email Address):');
							 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$work			= & $myTabData->createChild(htmlEleInput);
								$work->setAttribute(htmlAttrType, 'text');
								$work->setAttribute(htmlAttrName, 'user');
								$work->setAttribute(htmlAttrMaxLength, '240');
								$work->setAttribute(htmlAttrSize, $textareaWidth);
								$work->setAttribute(htmlAttrValue, $userName);
		$this->anchor->sessionDoc->putDialogVarTracker('user', 'Item'); // Catalog the dialog form variables

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, 'Pass-phrase:');
							 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$work			= & $myTabData->createChild(htmlEleInput);
								$work->setAttribute(htmlAttrType, 'password');
								$work->setAttribute(htmlAttrName, 'pswd');
								$work->setAttribute(htmlAttrMaxLength, '255');
								$work->setAttribute(htmlAttrSize, $textareaWidth);
								$work->setAttribute(htmlAttrValue, $userPswd);
		$this->anchor->sessionDoc->putDialogVarTracker('pswd', 'Item'); // Catalog the dialog form variables
////
		$regImageSrc  = $this->anchor->getActionUrlBase().'dialog='.$this->getPanelName().'&request='.AIR_Action_GetRegCode;

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, '');
							 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		// Create a copy of the image in order to determine the height and width
		// specifications so we can encode them on the HTML page
		$imageMask = new C_AirSecurityPrompt();
		$imageMask->initialize(AF_ROOT_DIR.'/images/regCodeMask.jpg');
		$height = $imageMask->getImageHeight();
		$width = $imageMask->getImageWidth();

		$image		= & $myTabData->createChild(htmlEleImg);
							 $image->setAttribute(htmlAttrSrc, $regImageSrc);
							 $image->setAttribute(htmlAttrAlt, 'Security Code Image');
							 $image->setAttribute(htmlAttrVAlign, 'center');
							 $image->setAttribute(htmlAttrHeight, $height);
							 $image->setAttribute(htmlAttrWidth, $width);

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, '');
							 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$workText	= 'The image above contains your security code. ';
		$workText  .= 'Please re-type the text code value from the image above into the text field below.';
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, $this->htmlLinewrap($workText));
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, 'Security Code:');
							 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->setAttribute(htmlAttrAlign, 'left');
		$work			= & $myTabData->createChild(htmlEleInput);
								$work->setAttribute(htmlAttrType, 'text');
								$work->setAttribute(htmlAttrName, 'regCode');
								$work->setAttribute(htmlAttrMaxLength, '255');
								$work->setAttribute(htmlAttrSize, $textareaWidth);
		$this->anchor->sessionDoc->putDialogVarTracker('regCode', 'Item'); // Catalog the dialog form variables
////
		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, '&#160;');
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->createChildSiteLink('Forgot your passphrase?', AIR_Action_Reset, null, 'LinkPrompt');
							 $myTabData->setAttribute(htmlAttrAlign, 'right');

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, '&#160;');
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->createChildSiteLink('Change passphrase', AIR_Action_Modify, null, 'LinkPrompt');
							 $myTabData->setAttribute(htmlAttrAlign, 'right');

		$pageBody					= & $myTable;
		$pageItem['itemBody']	= & $pageBody;

		$cmdTable = new C_AirHtmlElement($this);
		$cmdTable->initialize(htmlEleTable);

		$cmdTabRow		= & $cmdTable->createChild(htmlEleTblRow);
		$cmdTabData		= & $cmdTabRow->createChild(htmlEleTblData);
								 $cmdTabData->setAttribute(htmlAttrAlign, 'center');

		$cmdTabBtn		= & $cmdTabData->createChild(htmlEleInput);
								 $cmdTabBtn->setAttribute(htmlAttrType, 'submit');
								 $cmdTabBtn->setAttribute(htmlAttrName, 'request');
								 $cmdTabBtn->setAttribute(htmlAttrValue, AIR_Action_Okay);

		$cmdTabBtn		= & $cmdTabData->createChild(htmlEleInput);
								 $cmdTabBtn->setAttribute(htmlAttrType, 'reset');
								 $cmdTabBtn->setAttribute(htmlAttrValue, AIR_Action_Reset);

		$cmdTabBtn		= & $cmdTabData->createChild(htmlEleInput);
								 $cmdTabBtn->setAttribute(htmlAttrType, 'submit');
								 $cmdTabBtn->setAttribute(htmlAttrName, 'request');
								 $cmdTabBtn->setAttribute(htmlAttrValue, AIR_Action_Quit);

		$pageItem['itemFooter']	= & $cmdTable;

		$itemArray[]				= $pageItem;
		$this->panelItems 		= $itemArray;
		}

	} // end of class

?>