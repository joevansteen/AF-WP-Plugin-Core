<?php
/*
 * dlg_NewUser.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-16 JVS Begin test of new standalone PHP environment script
 * V1.3 2005-NOV-03 JVS Integration with old Tikiwiki AIR base
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirNewUserPanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirNewUserPanel extends C_HtmlPanel {
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

	  	$userName  = $this->anchor->getDlgVar('user');
	  	$userPswd  = $this->anchor->getDlgVar('pswd');
	  	$userPswd2 = $this->anchor->getDlgVar('pswd2');

//		$air_SelectMultiple		= $this->anchor->getDlgVar('air_SelectMultiple');
//		$air_EleType				= $this->anchor->getDlgVar('air_EleType');
//		$dlgPanelType				= $this->anchor->getDlgVar('dlgPanelType');
//		$panelItemTitle			= $this->anchor->getDlgVar('panelItemTitle');
//		$panelItemSubtitle		= $this->anchor->getDlgVar('panelItemSubtitle');
//		$air_selectedEleType		= $this->anchor->getDlgVar('air_selectedEleType');
		$air_showDiagnostics		= $this->anchor->getDlgVar('air_showDiagnostics');
		$air_showHelp				= $this->anchor->getDlgVar('air_showHelp');
		$resultDiag					= $this->anchor->getDlgVar('resultDiag');

		$helpText					= array();
		$helpItem					= array();
		$helpItem['msgItem'] = 'Pass-phrase: ';
		$helpItem['msgType'] = AIR_DiagMsg_Info;
			$content  = 'Pass-phrases should be created as easy to remember phrases that can be re-typed the same way at a later point. ';
			$content .= 'You may use up to 255 characters to create your phrase within the following policy:';
		$helpItem['msgText'] = $content;
		$helpText[] 			= $helpItem;
		$helpItem['msgItem'] = '&#160;';
		$helpItem['msgType'] = AIR_DiagMsg_Info;
			$content = '<ul>';
			$content .= '<li>Minimum size is 7 characters.</li>';
			$content .= '<li>Maximum size is 255 characters.</li>';
			$content .= '<li>Minimum of one lower case letter.</li>';
			$content .= '<li>Minimum of one upper case letter.</li>';
			$content .= '<li>Minimum of one numeric digit.</li>';
				$specials = '~!@#$%^&*_-+=:;,.?';
			$content .= '<li>Minimum of one of the following characters: "'.$specials.'".</li>';
			$content .= '</ul>';
		$helpItem['msgText'] = $content;
		$helpText[] 			= $helpItem;
//		$Ele_CreateEntity			= $this->anchor->getDlgVar('Ele_CreateEntity');
//		$Ele_CreateDt				= $this->anchor->getDlgVar('Ele_CreateDt');
//		$Ele_ChgType				= $this->anchor->getDlgVar('Ele_ChgType');
//		$Ele_ChgEntity				= $this->anchor->getDlgVar('Ele_ChgEntity');
//		$Ele_ChgDt					= $this->anchor->getDlgVar('Ele_ChgDt');
//		$Ele_ChgComments			= $this->anchor->getDlgVar('Ele_ChgComments');

//		$air_ItemHeader			= $this->anchor->getDlgVar('air_ItemHeader');
//		$air_Dialog					= $this->anchor->getDlgVar('air_Dialog');
//		$air_ItemFooter			= $this->anchor->getDlgVar('air_ItemFooter');

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

		$this->pageTitle = 'New Member Registration';

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleH1, null, 'New Member Registration');
		$this->panelTitle = & $myTitleData;

		$itemArray	= array();

		$pageItem	= array();

		if ($air_showDiagnostics)
			{
			$itemMessages	= array();
			foreach ($resultDiag as $msgItem)
				{
				$itemMessage	=	array();
				$itemMessage['msgItem']	= 	$msgItem['msgItem'];
				$itemMessage['msgText']	=	$msgItem['msgText'];
				if (array_key_exists('msgType', $msgItem))
					{
					$itemMessage['msgType']	= 	$msgItem['msgType'];
					}
				$itemMessages[]	= $itemMessage;
				}
			$pageItem['itemMessages']	= $itemMessages;
			}

		if ($air_showHelp)
			{
			$itemMessages	= array();
			foreach ($helpText as $msgItem)
				{
				$itemMessage	=	array();
				$itemMessage['msgItem']	= 	$msgItem['msgItem'];
				$itemMessage['msgText']	=	$msgItem['msgText'];
				if (array_key_exists('msgType', $msgItem))
					{
					$itemMessage['msgType']	= 	$msgItem['msgType'];
					}
				$itemMessages[]	= $itemMessage;
				}
			$pageItem['itemHelpText']	= $itemMessages;
			}

//		$pageItem['itemTitle']	= & $myTitleData;

//		$pageItem['itemHeader']	= & $this->formatStdDialogArray($air_ItemHeader);

		$myTable = new C_AirHtmlElement($this);
		$myTable->initialize(htmlEleTable);

		if ($air_showHelp)
			{
			$myTabRow	= & $myTable->createChild(htmlEleTblRow);
			$myTabData	= & $myTabRow->createChild(htmlEleTblData, 'Please provide a valid email address as a login ID, and select a passphrase.<br/>&#160;');
								 $myTabData->setAttribute(htmlAttrClass, 'CatchPhrase');
								 $myTabData->setAttribute(htmlAttrAlign, 'center');
								 $myTabData->setAttribute(htmlAttrColSpan, '2');
			}

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, '&#160;');
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->createChildSiteLink('Member Login', AIR_Action_Login, null, 'LinkPrompt');
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

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, 'Retype passphrase:');
							 $myTabData->setAttribute(htmlAttrAlign, 'right');
		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$work			= & $myTabData->createChild(htmlEleInput);
								$work->setAttribute(htmlAttrType, 'password');
								$work->setAttribute(htmlAttrName, 'pswd2');
								$work->setAttribute(htmlAttrMaxLength, '255');
								$work->setAttribute(htmlAttrSize, $textareaWidth);
								$work->setAttribute(htmlAttrValue, $userPswd2);
		$this->anchor->sessionDoc->putDialogVarTracker('pswd2', 'Item'); // Catalog the dialog form variables

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

		$myTabRow	= & $myTable->createChild(htmlEleTblRow);
		$myTabData	= & $myTabRow->createChild(htmlEleTblData, '&#160;');
							 $myTabData->setAttribute(htmlAttrAlign, 'left');

		$myTabData	= & $myTabRow->createChild(htmlEleTblData);
							 $myTabData->createChildSiteLink('Forgot your passphrase?', AIR_Action_Reset, null, 'LinkPrompt');
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

		$this->panelItems = $itemArray;

		$content  = '<center><table width="75%"><tr><td>';
		$content .= 'NOTE. This is a temporary procedure. Actual membership sign-up needs to include ini file ';
		$content .= 'management of policy options. These may include: ';
		$content .= '<ol>';
		$content .= '<li>No login. Entire site is open and access is anonymous</li>';
		$content .= '<li>User ID, but no passphrase. Shared ID provides access to role based views.</li>';
		$content .= '<li>ID and passphrase, with passphrase scheme defined by rules options (min and max sizes, special characters, etc).</li>';
		$content .= '</ol>';
		$content .= 'Signup options should include: ';
		$content .= '<ol>';
		$content .= '<li>Self sign-up. Create an ID and passphrase, and you are in, with some default membership role.</li>';
		$content .= '<li>Sign up request with automatic e-mail providing initial passphrase.</li>';
		$content .= '<li>Sign up request with filtered release of e-mail providing initial passphrase.</li>';
		$content .= '<li>Local sign up request with filtered activation providing initial access.</li>';
		$content .= '</ol>';
		$content .= 'Other features should include: ';
		$content .= '<ol>';
		$content .= '<li>Pass-phrase expiation after an ini value number of days.</li>';
		$content .= '<li>No reuse of last [option value] passphrases.</li>';
		$content .= '<li>Protection against robot signup (use of dynamic image that needs to be read and re-keyed).</li>';
		$content .= '<li>Initial passphrase generation by computer followed by email to client to validate the email address. Then forced passphrase selection on first sign-in</li>';
		$content .= '<li>Use of a special (system generated, complex) passphrase for first signin.</li>';
		$content .= '<ul>';
		$content .= '<li>First signin should be with a "get" posting that provides the sha1 value. This needs to be recognized when supplied so we '."don't".' double sha1 the value!</li>';
		$content .= '<li>A second option with this would be to also allow users to download their sha1 passphrase values for use with other secure GET transactions, including automated logon.</li>';
		$content .= '</ul>';
		$content .= '<li>Singleton user controls.</li>';
		$content .= '<li>A statement of review and compliance with site policies and legal statement, and peridic re-affirmation with "click-through" on subsequent logon.</li>';
		$content .= '<li>...</li>';
		$content .= '</ol>';
		$content .= '</td></tr></table></center>';

		$this->panelFooter = $content;
		}

	} // end of class
/*******************************************************************
 *******************************************************************/

 ?>