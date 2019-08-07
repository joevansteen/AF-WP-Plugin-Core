<?php
/*
 * af_viewMgr_dialogencode script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.0 2005-JUL-26 JVS Original code.
 *				-AUG-05 JVS Shaping. Change to perform output encoding
 *								process only.
 * V1.2 2005-SEP-08 JVS Code reshaping to utilize data (table) driven logic
 *                      to define data elements managed as part of
 *                      individual element type processing.
 * V1.3 2005-OCT-31 JVS Name change from air-html_encode to af_dialogencode
 *                      as part of extraction from TikiWiki framework.
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 *
 * This module is a first prototype version of a dynamically
 * loaded and invoked processing module for execution within
 * a PHP processing environment.
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
require(AF_AUTOMATED_SCRIPTS.'/af_viewMgr_base.php');
$myProcClass = 'C_ProcModDialogEncode';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * sortManifestItemsByType()
	 *******/
function sortManifestItemsByType($ele1, $ele2)
	{
	$sortResult = 0;

	$key1 = strtolower($ele1['Type']);
	$key2 = strtolower($ele2['Type']);

	if ($key1 == $key2)
		{
		$key1 = strtolower($ele1['Name']);
		$key2 = strtolower($ele2['Name']);

		if ($key1 == $key2)
			{
			$sortResult = 0;
			}
		else
		if ($key1 < $key2)
			{
			$sortResult = -1;
			}
		else
			{
			$sortResult = 1;
			}
		}
	else
	if ($key1 < $key2)
		{
		$sortResult = -1;
		}
	else
		{
		$sortResult = 1;
		}


	return ($sortResult);
	}

class C_ProcModDialogEncode extends C_ViewMgrBase {

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
	 * ProcMod_Main
	 *******/
	function ProcMod_Main(& $procContext, & $baseMsg, & $procMsg)
	 	{
	 	parent::initialize($procContext, $baseMsg, $procMsg);

		$this->hasSelectOptionNone	= true;
		$this->hasSelectOptionAny	= false;
		$this->hasSelectOptionAll	= false;

		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, $this->myMsgObject);
			}

		switch ($this->myMsgObject)
			{
			case Dialog_EleMaint:
				$result = $this->createTxnDataArrayFromProcMsg();
				if ($result < 0)
					{
					$this->anchor->putLogicCheck(__LINE__, __FILE__,
							'Critical data error in '.__FUNCTION__.' ['.$this->myMsgObject.']');
					}
				$this->procEleMaint();
				break;
			case Dialog_EleTypeMaint:
			case Dialog_PropTypeMaint:
			case Dialog_AssocTypeMaint:
			case Dialog_CoordTypeMaint:
			case Dialog_EleClassMaint:
			case Dialog_RelClassMaint:
				$result = $this->createTxnDataArrayFromProcMsg();
				if ($result < 0)
					{
					trigger_error('Critical data error in '.__FUNCTION__." [$this->myMsgObject]" , E_USER_NOTICE);
					}
				$this->procEleMaint();
				break;
			case Dialog_PropRuleMaint:
			case Dialog_AssocRuleMaint:
			case Dialog_CoordRuleMaint:
				$result = $this->createTxnDataArrayFromProcMsg();
				if ($result < 0)
					{
					trigger_error('Critical data error in '.__FUNCTION__." [$this->myMsgObject]" , E_USER_NOTICE);
					}
				$this->procEleMaint();
				break;
			case Dialog_PropMaint:
			case Dialog_AssocMaint:
			case Dialog_CoordMaint:
				$result = $this->createTxnDataArrayFromProcMsg();
				if ($result < 0)
					{
					trigger_error('Critical data error in '.__FUNCTION__." [$this->myMsgObject]" , E_USER_NOTICE);
					}
				$this->procEleMaint();
				break;
			case Dialog_EleList:
				$this->createMenuTxnSpecArray();
				$this->procEleList();
				break;
			case Dialog_EleListContext:
				$this->createMenuTxnSpecArray();
				$this->procEleListContext();
				break;
			case Dialog_ManifestReview:
			case Dialog_EleIndex:
				$this->procManifestReview();
				break;
			case Dialog_DirView:
				$this->procDirView();
				break;
			case Dialog_FileEdit:
			case Dialog_FileEditReview:
			case Dialog_FilePrint:
			case Dialog_FileView:
				$this->procFileMaint();
				break;
			case Dialog_FileCreate:
				$this->procFileCreate();
				break;
			case Dialog_FileUpload:
				$this->procFileUpload();
				break;
			case Dialog_GenRegCode:
				/*
				 * No action required. Just stage the response.
				 */
				$this->anchor->setDlgVar('responseDialog', $this->myMsgObject);
				break;
			default:
				$this->procDefault();
				break;
			}

		$this->anchor->setDlgVarByRef('air_ItemHeader', $this->dlgHeader);
		$this->anchor->setDlgVarByRef('air_Dialog', 		$this->dlgContent);
		$this->anchor->setDlgVarByRef('air_ItemFooter', $this->dlgFooter);
		}


	/***************************************************************************
	 * procEleMaint
	 *******/
	function procEleMaint()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->selectOnHeader		= true;
		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Bypass;
		$this->actionButtons[] 		= AIR_Action_Reset;
		$this->actionButtons[] 		= AIR_Action_Save;
		$this->actionButtons[] 		= AIR_Action_Quit;

		$this->showAirElementBaseInfo2();

		if (($this->myTxnSpec['TxnOper'] == AIR_Action_Add)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Load)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Modify)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_ModifyRaw))
			{
			$showAsArray = true;
			$showAsReadOnly = false;
			$this->anchor->setDlgVar('air_showSaveCommand', true);
			}
		else
			{
			$showAsArray = false;
			$showAsReadOnly = true;
			}

  		if (($this->myTxnSpec['TxnOper'] == AIR_Action_ShowRaw)
  		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_ModifyRaw))
	  		{
			if (! array_key_exists('EleContent', $this->myTxnSpec))
				{
				$this->myTxnSpec['EleContent'] = $this->procMsg->getMessageData('EleContent');
				}
			$this->setTextboxDisplay('Content',	'dlgEleContent', $this->myTxnSpec['EleContent'], $showAsReadOnly);
  			}
		else
  			{
			$this->createHtmlResponsePanelInfo($showAsArray, $showAsReadOnly);
			switch ($this->myTxnSpec['EleType'])
				{
				case AIR_EleType_PropType:
				case AIR_EleType_AssocType:
				case AIR_EleType_CoordType:
					$this->createRulesRelTypeXrefInfo($this->myContextObject, $showAsReadOnly);
					break;
				case AIR_EleType_EleType:
				default:
					$this->createRulesEleTypeXrefInfo($this->myContextObject, $showAsReadOnly);
					break;
				}
  			}

		$this->showDiagnosticsInfo();

		$txnAction = $this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);
		if (! empty($this->myTxnSpec['EleName']))
			{
			$maintItem = $this->myTxnSpec['EleName'];
			}
		else
			{
			$maintItem	 = '';
			$maintItemId = $this->myTxnSpec['EleType'];
 			if (! empty($maintItemId))
	 			{
		 		$maintItem = $this->anchor->Repository->getElementName($maintItemId);
	 			}
			}

		$this->anchor->setDlgVar('panelSubtitle', 'Business Model Maintenance');
		$this->anchor->setDlgVar('panelTitle',	 'Architecture Information Repository');
		$this->anchor->setDlgVar('panelSubtitle', 'Model Constraints Maintenance');
		$this->anchor->setDlgVar('panelSubtitle', '* Model Element Maintenance *');

//		$this->anchor->setDlgVar('panelItemTitle',	 'Model Element Maintenance');
		$this->anchor->setDlgVar('panelItemTitle',	 $txnAction . ' ' . $maintItem);
		$this->anchor->setDlgVar('panelItemSubtitle', $this->myContextObject);

		$this->anchor->setDlgVar('dlgPanelType',	 $this->myMsgObject);
		$this->anchor->setDlgVar('responseDialog', Dialog_EleMaint);
		}

	/***************************************************************************
	 * procEleList
	 *******/
	function procEleList()
		{
		$dlgChoice		= null;

//		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__
			                            .' using TxnOper='.$this->myTxnSpec['TxnOper']);
			}

		switch ($this->myTxnSpec['TxnOper'])
			{
			case AIR_Action_DirViewRaw:
				$this->actionButtons[] 		= AIR_Action_Delete;
//				$this->actionButtons[] 		= AIR_Action_Edit;
				$this->actionButtons[] 		= AIR_Action_View;
//				$this->actionButtons[] 		= AIR_Action_Print;
				break;
			case AIR_Action_Modify:
			case AIR_Action_ModifyRaw:
			case AIR_Action_CodeConvert:
			case AIR_Action_Add:
	   	case AIR_Action_PurgeType:
			case AIR_Action_DeleteItem:
			case AIR_Action_AuditItem:
			case AIR_Action_Load:
				$this->actionButtons[] 		= AIR_Action_Okay;
				break;
			case AIR_Action_Show:
			case AIR_Action_ShowItem:
			case AIR_Action_ShowRaw:
			default:
				$this->actionButtons[] 		= AIR_Action_Okay;
				break;
			}
		$this->actionButtons[] 		= AIR_Action_Reset;
		$this->actionButtons[] 		= AIR_Action_Quit;

		if (($this->myTxnSpec['TxnOper'] == AIR_Action_Add)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Load)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Modify)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_ModifyRaw))
			{
			$showAsArray = true;
			$showAsReadOnly = false;
			$showAsModifiable = true;
			}
		else
			{
			$showAsArray = false;
			$showAsReadOnly = true;
			$showAsModifiable = false;
			}

		switch ($this->myTxnSpec['TxnOper'])
			{
			case AIR_Action_Show:
	   	case AIR_Action_PurgeType:
				$this->anchor->setDlgVar('air_ShowKeys',			$this->procContext->getSessionData('UseKeyedLists'));
				$this->anchor->setDlgVar('air_ShowNumbered',		$this->procContext->getSessionData('UseNumberLists'));
				$this->anchor->setDlgVar('air_ShowChoice',		false);
				$this->anchor->setDlgVar('air_SelectMultiple',	false);
				break;
			case AIR_Action_DeleteItem:
			case AIR_Action_AuditItem:
			case AIR_Action_ShowItem:
			case AIR_Action_ShowRaw:
			case AIR_Action_Modify:
			case AIR_Action_ModifyRaw:
			case AIR_Action_CodeConvert:
			case AIR_Action_DirViewRaw:
				$this->anchor->setDlgVar('air_ShowNumbered',		false);
				$this->anchor->setDlgVar('air_ShowKeys',			$this->procContext->getSessionData('UseKeyedLists'));
				$this->anchor->setDlgVar('air_ShowChoice',		true);
				$this->anchor->setDlgVar('air_SelectMultiple',	true);
				$dlgChoice	= 'dlgChoice:';
				break;
			case AIR_Action_Add:
			case AIR_Action_Load:
			default:
				$this->anchor->setDlgVar('air_ShowNumbered',		false);
				$this->anchor->setDlgVar('air_ShowKeys',			false);
				$this->anchor->setDlgVar('air_ShowChoice',		true);
				$this->anchor->setDlgVar('air_SelectMultiple',	false);
				break;
			}

		$dlgContext	= $this->procContext->getContextId();
		$this->setShowSelectionInfo('Context', true, AIR_EleType_Context, $dlgContext, $showAsArray, $showAsModifiable);
		$this->myTxnSpec['EleClass']	= $this->procContext->getSessionData('dlgEleClass');
 		$this->setShowSelectionInfo('EleClass', true, AIR_EleType_EleClass, $this->myTxnSpec['EleClass'], $showAsArray, $showAsModifiable);
		$this->myTxnSpec['EleType']		= $this->procMsg->getMessageData('EleType');
 		$this->setShowSelectionInfo('EleType', true, AIR_EleType_EleType, $this->myTxnSpec['EleType'], $showAsArray, $showAsModifiable);

//		$dlgObject	= $this->procContext->getSessionData('dlgObject');

		$this->anchor->setDlgVar('air_showParent', false);
		$this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);

		$listEleType = $this->procMsg->getMessageData('EleType');
		$this->anchor->putTraceData(__LINE__, __FILE__, 'listEleType=['.$listEleType.']');
		$this->myTxnSpec['EleType']		= $this->procMsg->getMessageData('EleType');
		$listEleType = $this->myTxnSpec['EleType'];

		$listEleType = $this->myTxnSpec['EleType'];
		if ($this->myTxnSpec['TxnOper'] == AIR_Action_PurgeType)
			{
	   	$maxItems = 175;
	   	}
	   else
	   	{
	   	$maxItems = 0;
	   	}
	   if ($this->anchor->debug())
	   	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Debugging '.__CLASS__.'::'.__FUNCTION__.", dlgChoice=$dlgChoice");
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Debugging '.__CLASS__.'::'.__FUNCTION__.", EleType=".$this->procMsg->getMessageData('EleType'));
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Debugging '.__CLASS__.'::'.__FUNCTION__.", listEleType=$listEleType");
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Debugging '.__CLASS__.'::'.__FUNCTION__.", maxItems=$maxItems");
			}
		if ($this->myTxnSpec['TxnOper'] == AIR_Action_DirViewRaw)
			{
			$air_eleArray 	= $this->anchor->get_allElementsAdminArray($maxItems, $dlgChoice);
			}
		else
			{
			$air_eleArray 	= $this->anchor->get_allElementsByType($listEleType, $maxItems, $dlgChoice);
			}

		$skipped = 0;
		if ($this->myTxnSpec['TxnOper'] == AIR_Action_PurgeType)
			{
			/*
			 * The following test needs to be able to be overridden. If used, it also needs a place to write the
			 * message to. There is NO RESULT-MSG at this point in the processing! We are already at the point of
			 * formating the result of the last step that produced one!
			 */
//	   	if ($this->anchor->isCoreElementType($listEleType))
//	   		{
//				$this->resultMsg->attachDiagnosticTextItem('Element Type', 'Purge is not allowed on core element types');
//				$this->resultMsg->attachDiagnosticTextItem('Element Type', 'Constraint is temporarily overridden');
//	   		}
//	   	else
		   	{
		   	$skipItem = false;
				foreach ($air_eleArray as $key => $value)
					{
			   	$skipItem = false;
			   	$currentSession = $this->procContext->getDocumentId();
					switch ($listEleType)
						{
						case AIR_EleType_WebSession:
							if (($key == $this->anchor->mySessionId)
							 || ($key == $currentSession))
								{
								$air_eleArray[$key] = '*** Current Session ***';
						   	$skipItem = true;
						   	$skipped++;
								}
							break;
						case AIR_EleType_ArchMessage:
							if ($this->anchor->isCurrentMessage($key))
								{
								$air_eleArray[$key] = '*** Current Dialog Message ***';
						   	$skipItem = true;
						   	$skipped++;
								}
							break;
						}

					if (! $skipItem)
						{
						$this->procContext->appendSessionDataCollection('PurgeItem', $key);
						}
					}
		   	}
			}

		$entries 		= count($air_eleArray);
		if ($entries <= $skipped)
			{
			unset($air_eleArray);
			}
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Setting air_eleArray with '.$entries.' elements');
		$this->anchor->setDlgVarByRef('air_eleArray', $air_eleArray);


		$this->showDiagnosticsInfo();

		$this->anchor->setDlgVar('responseDialog', Dialog_EleList);
		}

	/***************************************************************************
	 * procEleListContext
	 *******/
	function procEleListContext()
		{
		$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);

		$this->actionButtons[] 		= AIR_Action_Save;
		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;
		$this->actionButtons[] 		= AIR_Action_Quit;

		$this->showDiagnosticsInfo();

		$this->anchor->setDlgVar('responseDialog', Dialog_EleList);
		}

	/***************************************************************************
	 * procFileMaint
	 *******/
	function procFileMaint()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$writable	= $this->procContext->getSessionData('DirMaintFileWrite');

		switch ($this->myMsgObject)
			{
			case Dialog_FileEdit:
				$this->actionButtons[] 		= AIR_Action_Review;
				$this->actionButtons[] 		= AIR_Action_Post;
				$this->actionButtons[] 		= AIR_Action_View;
				$this->actionButtons[] 		= AIR_Action_Print;
				$this->actionButtons[] 		= AIR_Action_Reset;
				$this->actionButtons[] 		= AIR_Action_Quit;
				break;
			case Dialog_FileEditReview:
				$this->actionButtons[] 		= AIR_Action_Post;
				$this->actionButtons[] 		= AIR_Action_Edit;
				$this->actionButtons[] 		= AIR_Action_View;
				$this->actionButtons[] 		= AIR_Action_Print;
				$this->actionButtons[] 		= AIR_Action_Reset;
				$this->actionButtons[] 		= AIR_Action_Quit;
				break;
			case Dialog_FilePrint:
				$this->actionButtons[] 		= AIR_Action_Okay;
				$this->actionButtons[] 		= AIR_Action_Reset;
				if ($writable)
					{
					$this->actionButtons[] 		= AIR_Action_Edit;
					}
				$this->actionButtons[] 		= AIR_Action_Quit;
				break;
			case Dialog_FileView:
				$this->actionButtons[] 		= AIR_Action_Quit;
				if ($writable)
					{
					$this->actionButtons[] 		= AIR_Action_Edit;
					}
				break;
			default:
				$this->actionButtons[] 		= AIR_Action_Okay;
				break;
			}

		$this->anchor->setDlgVar('OrigContent', $this->procContext->getSessionData('DirMaintFileData'));
		$this->anchor->setDlgVar('FileContent', $this->procContext->getSessionData('DirMaintEditData'));
		$this->anchor->setDlgVar('Filename', $this->procContext->getSessionData('DirMaintFileName'));
		$this->anchor->setDlgVar('Filecoding', $this->procContext->getSessionData('DirMaintFileCoding'));
		$this->anchor->setDlgVar('Filemime', $this->procContext->getSessionData('DirMaintFileMime'));
		$this->anchor->setDlgVar('Filetype', $this->procContext->getSessionData('DirMaintFileType'));
		$this->anchor->setDlgVar('Filesize', $this->procContext->getSessionData('DirMaintFileSize'));
		$this->anchor->setDlgVar('Filemtime', $this->procContext->getSessionData('DirMaintFileMtime'));
		$this->anchor->setDlgVar('Fileatime', $this->procContext->getSessionData('DirMaintFileAtime'));
		$this->anchor->setDlgVar('Fileperms', $this->procContext->getSessionData('DirMaintFilePerms'));
		$this->anchor->setDlgVar('Readable', $this->procContext->getSessionData('DirMaintFileRead'));
		$this->anchor->setDlgVar('Writable', $writable);

		$this->showDiagnosticsInfo();

		$this->anchor->setDlgVar('responseDialog', $this->myMsgObject);
		}

	/***************************************************************************
	 * procFileCreate
	 *******/
	function procFileCreate()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		switch ($this->myMsgObject)
			{
			case Dialog_FileCreate:
				$this->actionButtons[] 		= AIR_Action_Create;
				$this->actionButtons[] 		= AIR_Action_Reset;
				$this->actionButtons[] 		= AIR_Action_Quit;
				break;
			default:
				$this->actionButtons[] 		= AIR_Action_Okay;
				break;
			}

		$this->anchor->setDlgVar('air_ContentType', $this->anchor->getStdVar('mimeTypeList'));
		$this->anchor->setDlgVar('FileContent', $this->procContext->getSessionData('DirMaintEditData'));

//		$air_ContentType 	= $this->anchor->getStdVar('mimeTypeList');
//		$content				= $this->procContext->getSessionData('DirMaintEditData');
//		$this->anchor->setDlgVar('air_ContentType', $air_ContentType);
//		$this->anchor->setDlgVar('FileContent', $content);
		$this->anchor->setDlgVar('Filename', $this->procContext->getSessionData('DirMaintFileName'));
		$this->anchor->setDlgVar('Filetype', $this->procContext->getSessionData('DirMaintFileType'));
		$this->anchor->setDlgVar('Filecoding', $this->procContext->getSessionData('DirMaintFileCoding'));
		$this->anchor->setDlgVar('Filemime', $this->procContext->getSessionData('DirMaintFileMime'));
		$this->anchor->setDlgVar('Filesize', $this->procContext->getSessionData('DirMaintFileSize'));

		$this->showDiagnosticsInfo();

		$this->anchor->setDlgVar('responseDialog', $this->myMsgObject);
		}

	/***************************************************************************
	 * procFileUpload
	 *******/
	function procFileUpload()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		switch ($this->myMsgObject)
			{
			case Dialog_FileUpload:
				$this->actionButtons[] 		= AIR_Action_Okay;
				$this->actionButtons[] 		= AIR_Action_Reset;
				$this->actionButtons[] 		= AIR_Action_Quit;
				break;
			default:
				$this->actionButtons[] 		= AIR_Action_Okay;
				break;
			}

		$this->anchor->setDlgVar('FileContent', $this->procMsg->getMessageData('FileContent'));
		$this->anchor->setDlgVar('Filename', $this->procMsg->getMessageData('Filename'));
		$this->anchor->setDlgVar('Filetype', $this->procMsg->getMessageData('Filetype'));
		$this->anchor->setDlgVar('Filesize', $this->procMsg->getMessageData('Filesize'));
		$this->anchor->setDlgVar('Filemtime', $this->procMsg->getMessageData('Filemtime'));
		$this->anchor->setDlgVar('Fileatime', $this->procMsg->getMessageData('Fileatime'));
		$this->anchor->setDlgVar('Fileperms', $this->procMsg->getMessageData('Fileperms'));
		$this->anchor->setDlgVar('Readable', $this->procMsg->getMessageData('Readable'));
		$this->anchor->setDlgVar('Writable', $this->procMsg->getMessageData('Writable'));

		$this->showDiagnosticsInfo();

		$this->anchor->setDlgVar('responseDialog', $this->myMsgObject);
		}

	/***************************************************************************
	 * procManifestReview
	 *******/
	function procManifestReview()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$this->actionButtons[] 		= AIR_Action_Okay;
		$this->actionButtons[] 		= AIR_Action_Quit;

		$cvtContextAction	= $this->procContext->getSessionData('cvtContextAction');
		$collectionSize	= $this->procContext->getDataCollectionItemCount('ConvertItem');

		if ((($cvtContextAction == AIR_Action_IdentConvert)
		  || ($cvtContextAction == AIR_Action_Execute))
		 && ($collectionSize > 0))
			{
			$this->actionButtons[]	= AIR_Action_Continue;
			}

		$listItems	= array();
		$this->anchor->setDlgVar('panelItemTitle', $this->procMsg->getMessageData('DirLabel'));
		$typeList = $this->anchor->get_allElementsByType(AIR_EleType_EleType);
		$this->anchor->setDlgVarByRef('air_listTypeArray',	$typeList);

		$collectionSize = $this->procMsg->getDataCollectionItemCount('Item');
		if ($collectionSize > 0)
			{
			// Found, post the data
//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Collection has $collectionSize items");
			for ($i = 0; $i < $collectionSize; $i++)
				{
				$collectionItemNode 		= $this->procMsg->getDataCollectionItem('Item', $i);
				$listItem 					= array();

				$listItem['OldKey'] 		= $collectionItemNode->getChildContentByName('OldKey');
				$listItem['NewKey'] 		= $collectionItemNode->getChildContentByName('NewKey');
				$listItem['Name'] 		= $collectionItemNode->getChildContentByName('Name');
				$itemType					= $collectionItemNode->getChildContentByName('Type');
				$listItem['Type'] 		= $typeList[$itemType];
				$listItem['Assigned'] 	= $collectionItemNode->getChildContentByName('Assigned');
				$listItem['Created'] 	= $collectionItemNode->getChildContentByName('Created');
				$listItem['Reviewed'] 	= $collectionItemNode->getChildContentByName('Reviewed');
				$listItem['Converted'] 	= $collectionItemNode->getChildContentByName('Converted');

				$listItems[] 				= $listItem;
				}

			usort($listItems, 'sortManifestItemsByType');
			}

		$this->showDiagnosticsInfo();
		$this->anchor->setDlgVarByRef('air_listItemArray',	$listItems);

		$this->anchor->setDlgVar('responseDialog', $this->myMsgObject);
		}

	/***************************************************************************
	 * procDirView
	 *******/
	function procDirView()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		$currentDirViewSet = $this->procContext->getSessionData('DirMaintDirType');
		if ($currentDirViewSet != AIR_Action_DirViewPub)
			{
			$this->actionButtons[] 		= AIR_Action_New;
			$this->actionButtons[] 		= AIR_Action_Upload;
			}
		$this->actionButtons[] 		= AIR_Action_Quit;

		$listItems	= array();
		$this->anchor->setDlgVar('panelItemTitle', $this->procMsg->getMessageData('DirLabel'));
		$collectionSize = $this->procMsg->getDataCollectionItemCount('Item');
		if ($collectionSize > 0)
			{
			// Found, post the data
//			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " Collection has $collectionSize items");
			for ($i = 0; $i < $collectionSize; $i++)
				{
				$collectionItemNode = $this->procMsg->getDataCollectionItem('Item', $i);
				$itemFile		= $collectionItemNode->getChildContentByName('File');
				$itemFiletype	= $collectionItemNode->getChildContentByName('Filetype');
				$itemFilesize	= $collectionItemNode->getChildContentByName('Filesize');
				$itemFiletime	= $collectionItemNode->getChildContentByName('Filetime');
				$itemReadable	= $collectionItemNode->getChildContentByName('Readable');
				$itemWritable	= $collectionItemNode->getChildContentByName('Writable');

				$listItem = array();
				$listItem['File'] 		= $itemFile;
				$listItem['Filetype'] = $itemFiletype;
				$listItem['Filesize'] = $itemFilesize;
				$listItem['Filetime'] = $itemFiletime;
				$listItem['Readable'] = $itemReadable;
				$listItem['Writable'] = $itemWritable;
				$listItems[] 			= $listItem;
				}
			}

		$this->showDiagnosticsInfo();
		$this->anchor->setDlgVarByRef('air_listItemArray',	$listItems);

		$this->anchor->setDlgVar('responseDialog', $this->myMsgObject);
		}

	/***************************************************************************
	 * procDefault
	 *******/
	function procDefault()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__.'::'.__FUNCTION__.' found unknown target: '.$this->myMsgObject);
			}

		$this->actionButtons[] 		= AIR_Action_Submit;
		$this->actionButtons[] 		= AIR_Action_Reset;

		$this->showDiagnosticsInfo();

		$this->anchor->setDlgVar('responseDialog', $this->myMsgObject);
		}

	/***************************************************************************
	 * createHtmlResponsePanelInfo
	 *******/
	function createHtmlResponsePanelInfo($showAsArray, $showAsReadOnly)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$eleModel = $this->anchor->Repository->getElementModel($this->myTxnSpec['EleType']);
		$eleSpec = $eleModel->getRuleArray();
		if ((!is_array($eleSpec))
		 || (empty($eleSpec)))
			{
			trigger_error('Critical data error in '.__FUNCTION__, E_USER_NOTICE);
			}

		foreach ($eleSpec as $eleItem)
		 	{
		 	$itemExists			= true;
			$itemIsElement		= $eleItem['elementSpec'];
		 	$itemName 			= $eleItem['FldName'];
			$dlgItemName		= 'dlg'.$itemName;
		 	$itemSpecType		= $eleItem['specType'];
			$itemVisible		= $eleItem['Visible'];
			$itemLabel			= $eleItem['Label'];
			$itemManualInput	= $eleItem['Manual'];
			$itemOrdSpec		= $eleItem['PredOrdSpec'];
			$itemCardSpec		= $eleItem['PredCardSpec'];
			$itemCardLimit		= $eleItem['PredCardLimit'];
			$itemDataType		= $eleItem['DataType'];
			$itemDataCaptType	= $eleItem['DataCaptType'];
			$itemDefault		= $eleItem['Default'];
			$itemSelType		= $eleItem['SelectionType'];

			$itemRelClass		= $eleItem['RelClass'];
			$itemExternal		= $eleItem['External'];
			$itemPlugInMod		= $eleItem['PlugInMod'];
			$itemSortKey		= $eleItem['RelSortKey'];
			$itemSelTypeFld	= $eleItem['SelTypeFld'];

		 	if (($itemIsElement)
		 	 && ($itemVisible))
		 		{
		 		/*
		 		 * If the item does not exist, create a default value
		 		 * and flag the fact that it was artificially generated.
		 		 */
				if ((! array_key_exists($itemName, $this->myTxnSpec))
				 || (empty($this->myTxnSpec[$itemName])))
					{
					$itemExists	= false;
					$this->myTxnSpec[$itemName] = $this->getTxnDataDefaultValue($eleItem);
					}

				/*
				 * Now, based on the data type and capture type, and taking into account
				 * the purpose of the output display (read only viewing versus data capture),
				 * formulate the display scheme for the element.
				 */
				switch ($itemDataCaptType)
					{
					case AIR_CaptureType_EleSelect:
						// $itemSelType defines an element type group that the selection
						// needs to be made from
//						$this->setSelectionListInfo($dlgItemLabel, $varNameBase,
//															$dlgSelectionType, $dlgSelectedValue,
//															$showAsArray = true, $showAsReadOnly = false)

						$itemValue = $this->myTxnSpec[$itemName];
						if ((! $showAsReadOnly)
						 || ((! empty($itemValue))
						  && ($itemValue != AIR_Null_Identifier)))
							{
							$this->hasSelectOptionNone	= ($itemOrdSpec != AIR_EleIsRequired);
							$this->hasSelectOptionAny	= false;
							$this->hasSelectOptionAll	= false;
							$this->setSelectionListInfo($itemLabel, $itemName,
																 $itemSelType, $itemValue,
																 ($showAsArray and $itemManualInput),
																 ($showAsReadOnly or (! $itemManualInput)));
							}
						break;
					case AIR_CaptureType_EleSpec:
						// $itemSelType defines an the element UUID. The element is a constant.
						// There is no selection to be made.
						if ((! $showAsReadOnly)
						 || ((! empty($itemSelType))
						  && ($itemSelType != AIR_Null_Identifier)))
							{
					 		$textValue = '';
					 		if (! empty($itemSelType))
					 			{
						 		$textValue = $this->anchor->Repository->getElementName($itemSelType);
						 		}
							$this->setKeyedTextDisplay($itemLabel, $dlgItemName, $itemSelType, $textValue);
							}
						break;
					case AIR_CaptureType_EleRef:
						// The element defines a UUID. The element is maintained internally. It is
						// display only. There is no selection to be made.
						$itemValue = $this->myTxnSpec[$itemName];
						if ((! $showAsReadOnly)
						 || ((! empty($itemValue))
						  && ($itemValue != AIR_Null_Identifier)))
							{
					 		$textValue = '';
					 		if (! empty($itemValue))
					 			{
						 		$textValue = $this->anchor->Repository->getElementName($itemValue);
						 		}
							$this->setKeyedTextDisplay($itemLabel, $dlgItemName, $itemValue, $textValue);
							}
						break;
					case AIR_CaptureType_CheckList:
							// Get the list of keys for the items in the collection
							$selTypes 	= $this->anchor->get_allElementsByType($eleItem['SelectionType'], 0, NULL, false,
																	false,	//		$this->hasSelectOptionNone,
																	false,	//		$this->hasSelectOptionAny,
																	true);	// 	$this->hasSelectOptionAll);
							// Get addressability on the txnSpec array sub-array
							$eleContent = $this->myTxnSpec[$itemName];
							if (! empty($eleContent))
								{
								if (! is_array($eleContent))
									{
									trigger_error("myTxnSpec[$itemName] content was not an array!", E_USER_NOTICE);
									$this->diagnoseTxnDataArray();
									}
								}
//		$this->anchor->putTraceData(__LINE__, __FILE__, __CLASS__ . '::' . __FUNCTION__ . " found txnSpec content as:");
//		foreach ($eleContent as $key => $value)
//			{
//			$this->anchor->putTraceData(__LINE__, __FILE__, "  key=[$key] value =[$value]");
//			}
							if (! $showAsReadOnly)
								{
								$itemArray = array();
								$itemArray['itemName']		= $dlgItemName . 'ShowAll';
								$itemArray['itemContent']	= 'Show unselected items'; // was $itemLabel
								$checklistShowAll				= $this->myTxnSpec[$itemName . 'ShowAll'];
								$itemArray['itemSelect']	= $checklistShowAll;
								$chklistArray = array();
								$chklistArray[] = $itemArray;
								$this->setCheckOptDisplay($itemLabel, $dlgItemName, $chklistArray, false);
								$itemLabel = '';
								}
							else
								{
								$checklistShowAll				= false;
								}
							$chklistArray = array();
							foreach ($selTypes as $key => $value)
								{
								$itemArray = array();
								$dlgName = 'dlg' . $itemName . $key;
								$itemArray['itemName']		 = $dlgName;
								$itemArray['itemContent']	 = $value;
								if ((! empty($eleContent))
								 && (array_key_exists($key, $eleContent)))
									{
									$itemCheckboxValue	= $eleContent[$key];
									}
								else
									{
									$itemCheckboxValue	= false;
									}
								$itemArray['itemSelect']	= $itemCheckboxValue;
								if (($checklistShowAll)
								 || ($itemCheckboxValue))
									{
									$chklistArray[] = $itemArray;
									}
								}
							if ((! $showAsReadOnly)
							 || (! empty($chklistArray)))
								{
								$this->setCheckOptDisplay($itemLabel, $dlgItemName, $chklistArray);
//								$this->setMultiSelectDisplay($itemLabel, $dlgItemName, $chklistArray);
								}

						break;

					case AIR_CaptureType_RelEleSel:
						// $itemSelTypeFld defines the item name of the variable that contains
						// the UUID of the element type group that the selection needs to be made from.
						// If the variable does not exist, or if it is empty or NULL, then no selection
						// can be made. Instead, the field value and display/capture method will be
						// determined based on the data type.
						$this->hasSelectOptionNone	= ($itemOrdSpec != AIR_EleIsRequired);
						$this->hasSelectOptionAny	= false;
						$this->hasSelectOptionAll	= false;
						if (array_key_exists($itemSelTypeFld, $this->myTxnSpec))
							{
							$selItemType = $this->myTxnSpec[$itemSelTypeFld];
							if ((!empty($selItemType))
							 && ($selItemType != AIR_Null_Identifier))
								{
								$itemValue = $this->myTxnSpec[$itemName];
								$this->setSelectionListInfo($itemLabel, $itemName,
																	 $selItemType, $itemValue,
																	 ($showAsArray and $itemManualInput),
																	 ($showAsReadOnly or (! $itemManualInput)));
								break;
								}
							}
					default:
						/*
						 * Remaining items should be "key entry" and are parsed by data type
						 */
						switch ($itemDataType)
							{
							case AIR_ContentType_Boolean:
								if (($showAsReadOnly )
								 || (! $itemManualInput))
									{
									if ($this->myTxnSpec[$itemName])
										{
										$this->setTextDisplay($itemLabel, $dlgItemName, 'TRUE',
																	($showAsReadOnly or (! $itemManualInput)));
										}
									else
										{
										$this->setTextDisplay($itemLabel, $dlgItemName, 'FALSE',
																	($showAsReadOnly or (! $itemManualInput)));
										}
									}
								else
									{
									// Boolean is displayed as single element checklist
									$itemArray = array();
									$itemArray['itemName']		 = $dlgItemName;
									$itemArray['itemContent']	 = 'True / Yes'; // was $itemLabel
									$itemArray['itemSelect']	 = $this->myTxnSpec[$itemName];
									$chklistArray = array();
									$chklistArray[] = $itemArray;
									$this->setCheckOptDisplay($itemLabel, $dlgItemName, $chklistArray, false);
									}
								break;

							case AIR_ContentType_IntText:
							case AIR_ContentType_IntHyperlink:
							case AIR_ContentType_Integer:
							case AIR_ContentType_Float:
							case AIR_ContentType_Date:
							case AIR_ContentType_Time:
							case AIR_ContentType_Datetime:
							case AIR_ContentType_UUID:
								$fldContent = $this->myTxnSpec[$itemName];
								/*
								 * The following test should include an additional item to be able to
								 * force display of blank fields under "show" (read only) conditions.
								 * A form of "show all" to forcefully display empty contents on those
								 * properties that are allowed, but have no information content.
								 */
								if ((! $showAsReadOnly)
								 || (! empty($fldContent)))
								 	{
									$this->setTextDisplay($itemLabel, $dlgItemName, $fldContent,
																($showAsReadOnly or (! $itemManualInput)));
									}
								break;
							case AIR_ContentType_IntTextBlock:
								$fldContent = $this->myTxnSpec[$itemName];
								/*
								 * Same comment as 'text' above.
								 */
								if ((! $showAsReadOnly)
								 || (! empty($fldContent)))
								 	{
									$this->setTextboxDisplay($itemLabel, $dlgItemName, $this->myTxnSpec[$itemName],
																($showAsReadOnly or (! $itemManualInput)));
									}
								break;
							case AIR_ContentType_ExtText:
							case AIR_ContentType_ExtTextBlock:
							case AIR_ContentType_ExtHyperlink:
							case AIR_ContentType_Binary:
							case AIR_ContentType_OrdSpec:
							default:
								$this->myTxnSpec[$itemName] = '';
								break;
							}
						break;
					}
				}
		 	}

		return;
		}

	/***************************************************************************
	 * createRulesEleTypeXrefInfo
	 *******/
	function createRulesEleTypeXrefInfo($eleIdent, $showAsReadOnly)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$ruleCount	= $this->anchor->Repository->airDB->getCount_AirRelRules();

		$ruleArray = $this->anchor->Repository->airDB->get_RelRulesUsingElement($eleIdent, $ruleCount);
		if ((isset($ruleArray))
		 && (is_array($ruleArray))
		 && (!empty($ruleArray)))
			{
			$predArray	= array();
			$objArray	= array();
			$iobjArray	= array();
			$diagArray	= array();

			$rowCount 	= count($ruleArray);
			if ($rowCount > 0)
				{
				/* examine the rowset */
				$rowNumber = 0;
				foreach ($ruleArray as $dbRow)
					{
					$rowNumber++;
					$ruleIdent						= $dbRow['Air_Ele_Id'];
					$ruleDoc 						= & $this->anchor->Repository->getElementRef($ruleIdent);
					$predicateId					= '';
					$objectId						= '';
					$iObjectId						= '';
					$diagId							= '';
					if (array_key_exists('Air_RelRule_Predicate', $dbRow))
						{
						$assocData					= array();
						$assocSeq					= $rowNumber * 5;
						$predicateId				= $dbRow['Air_RelRule_Predicate'];
						if ((!empty($predicateId))
						 && ($predicateId != AIR_Null_Identifier))
							{
							$assocData['seq']				= $assocSeq;
							$assocData['item']			= $ruleDoc->getElementData('PredType');
							$assocData['itemName']		= $this->anchor->Repository->getElementName($predicateId);
							$assocData['external']		= $ruleDoc->getElementData('External');
							$assocData['ordinality']	= $ruleDoc->getElementData('PredOrdSpec');
							$assocData['cardinality']	= $ruleDoc->getElementData('PredCardSpec');
							$assocData['maxOccurs']		= $ruleDoc->getElementData('PredCardLimit');
							$assocData['diag']			= $ruleDoc->getElementData('RuleDiag');
							$predArray[$assocSeq]		= $assocData;
							}
						}
					if (array_key_exists('Air_RelRule_Object', $dbRow))
						{
						$objectId						= $dbRow['Air_RelRule_Object'];
						if ((!empty($objectId))
						 && ($objectId != AIR_Null_Identifier))
							{
							$objArray[$objectId]			= $this->anchor->Repository->getElementName($objectId);
							}
						}
					if (array_key_exists('Air_RelRule_IObject', $dbRow))
						{
						$iObjectId						= $dbRow['Air_RelRule_IObject'];
						if ((!empty($iObjectId))
						 && ($iObjectId != AIR_Null_Identifier))
							{
							$iobjArray[$iObjectId]		= $this->anchor->Repository->getElementName($iObjectId);
							}
						}
					if (array_key_exists('Air_RelRule_Diag', $dbRow))
						{
						$diagId						= $dbRow['Air_RelRule_Diag'];
						if ((!empty($diagId))
						 && ($diagId != AIR_Null_Identifier))
							{
							$diagArray[$diagId]		= $this->anchor->Repository->getElementName($diagId);
							}
						}
//					$result							= array();
//					$result['specType'] 			= 'basic';
//					$result['elementSpec']		= true;
//
//					$result['RuleIdent'] 		= $dbRow['Air_Ele_Id'];
//					$result['PredType'] 			= $dbRow['Air_RelRule_Predicate'];
//					$result['SubjType'] 			= $dbRow['Air_RelRule_Subject'];
//					$result['PredOrdSpec'] 		= $dbRow['Air_RelRule_PredOrd'];
//					$result['PredCardSpec'] 	= $dbRow['Air_RelRule_PredCard'];
//					$result['PredCardLimit']	= $dbRow['Air_RelRule_PredMax'];
					}
				}

			if (! empty($predArray))
				{
//				ksort($predArray);
				$this->setPropertyListDisplay('Attributes', '', $predArray);
				}
			if (! empty($objArray))
				{
				asort($objArray);
				$this->setSimpleListDisplay('Relates to', '', $objArray);
				}
			if (! empty($iobjArray))
				{
				asort($iobjArray);
				$this->setSimpleListDisplay('Relates via', '', $iobjArray);
				}
			if (! empty($diagArray))
				{
				asort($diagArray);
				$this->setSimpleListDisplay('Diagnostics', '', $diagArray);
				}
//			function setSimpleListDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent, $ordered=true)
			}

		return;
		}

	/***************************************************************************
	 * createRulesRelTypeXrefInfo
	 *******/
	function createRulesRelTypeXrefInfo($eleIdent, $showAsReadOnly)
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$ruleCount	= $this->anchor->Repository->airDB->getCount_AirRelRules();
		$ruleArray = $this->anchor->Repository->airDB->get_RelRulesUsingPredicate($eleIdent, $ruleCount);
		if ((isset($ruleArray))
		 && (is_array($ruleArray))
		 && (!empty($ruleArray)))
			{
			$subjArray	= array();
			$objArray	= array();
			$iobjArray	= array();
			$diagArray	= array();

			$rowCount 	= count($ruleArray);
			if ($rowCount > 0)
				{
				/* examine the rowset */
				foreach ($ruleArray as $dbRow)
					{
					$subjectId						= '';
					$objectId						= '';
					$iObjectId						= '';
					$diagId							= '';
					if (array_key_exists('Air_RelRule_Subject', $dbRow))
						{
						$subjectId						= $dbRow['Air_RelRule_Subject'];
						if ((!empty($subjectId))
						 && ($subjectId != AIR_Null_Identifier))
							{
							$subjArray[$subjectId]		= $this->anchor->Repository->getElementName($subjectId);
							}
						}
					if (array_key_exists('Air_RelRule_Object', $dbRow))
						{
						$objectId						= $dbRow['Air_RelRule_Object'];
						if ((!empty($objectId))
						 && ($objectId != AIR_Null_Identifier))
							{
							$objArray[$objectId]			= $this->anchor->Repository->getElementName($objectId);
							}
						}
					if (array_key_exists('Air_RelRule_IObject', $dbRow))
						{
						$iObjectId						= $dbRow['Air_RelRule_IObject'];
						if ((!empty($iObjectId))
						 && ($iObjectId != AIR_Null_Identifier))
							{
							$iobjArray[$iObjectId]		= $this->anchor->Repository->getElementName($iObjectId);
							}
						}
					if (array_key_exists('Air_RelRule_Diag', $dbRow))
						{
						$diagId						= $dbRow['Air_RelRule_Diag'];
						if ((!empty($diagId))
						 && ($diagId != AIR_Null_Identifier))
							{
							$diagArray[$diagId]		= $this->anchor->Repository->getElementName($diagId);
							}
						}
//					$result							= array();
//					$result['specType'] 			= 'basic';
//					$result['elementSpec']		= true;
//
//					$result['RuleIdent'] 		= $dbRow['Air_Ele_Id'];
//					$result['PredType'] 			= $dbRow['Air_RelRule_Predicate'];
//					$result['SubjType'] 			= $dbRow['Air_RelRule_Subject'];
//					$result['PredOrdSpec'] 		= $dbRow['Air_RelRule_PredOrd'];
//					$result['PredCardSpec'] 	= $dbRow['Air_RelRule_PredCard'];
//					$result['PredCardLimit']	= $dbRow['Air_RelRule_PredMax'];
					}
				}

			if (! empty($subjArray))
				{
				asort($subjArray);
				$this->setSimpleListDisplay('Attribute of', '', $subjArray);
				}
			if (! empty($objArray))
				{
				asort($objArray);
				$this->setSimpleListDisplay('Link to', '', $objArray);
				}
			if (! empty($iobjArray))
				{
				asort($iobjArray);
				$this->setSimpleListDisplay('Link via', '', $iobjArray);
				}
			if (! empty($diagArray))
				{
				asort($diagArray);
				$this->setSimpleListDisplay('Diagnostics', '', $diagArray);
				}
//			function setSimpleListDisplay($dlgItemLabel, $dlgItemName, $dlgItemContent, $ordered=true)
			}

		return;
		}

	/***************************************************************************
	 * showAirElementBaseInfo()
	 *
	 * Create SMARTY template trigger data to show diagnostics and/or routine
	 * transaction results.
	 *******/
	function showAirElementBaseInfo()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (($this->myTxnSpec['TxnOper'] == AIR_Action_Add)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Load)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Modify)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_ModifyRaw))
			{
			$showAsArray = true;
			$showAsReadOnly = false;
			$showAsModifiable = true;
			}
		else
			{
			$showAsArray = false;
			$showAsReadOnly = true;
			$showAsModifiable = false;
			}

		/*
		 * Create context array and post context display information to SMARTY
		 */
		$dlgContext	= $this->procContext->getContextId();
		$this->setShowSelectionInfo('Context', true, AIR_EleType_Context, $dlgContext, $showAsArray, $showAsModifiable);
		switch ($this->myTxnSpec['EleType'])
			{
			case AIR_EleType_EleType:
				$this->anchor->setDlgVar('air_showParent', false);
		 		$this->setShowSelectionInfo('EleClass', true, AIR_EleType_EleClass, $this->myTxnSpec['EleClass'], true, false);
				break;
			case AIR_EleType_EleClass:
				$this->anchor->setDlgVar('air_showParent', true);
		 		$this->setShowSelectionInfo('EleClass', true, AIR_EleType_EleClass, $this->myTxnSpec['EleClass'], true, false);
				break;
			default:
				$this->anchor->setDlgVar('air_showParent', false);
		 		$this->setShowSelectionInfo('EleClass', true, AIR_EleType_EleClass, $this->myTxnSpec['EleClass'], false, false);
				break;
			}

 		$this->setShowSelectionInfo('EleType', true, AIR_EleType_EleType, $this->myTxnSpec['EleType'], $showAsArray, $showAsModifiable);

  		if (($this->myContextAction == AIR_Action_ShowItem)
  		 || ($this->myContextAction == AIR_Action_ShowRaw))
			{
			$dlgDataCaptureOper	= false;
			}
		else
		 	{
			$dlgDataCaptureOper	= true;
			}
		$this->anchor->setDlgVar('air_dataCaptureOper', $dlgDataCaptureOper);

		switch ($this->myContextAction)
		  	{
		  	case AIR_Action_Modify:
		  	case AIR_Action_ModifyRaw:
		  	case AIR_Action_ShowItem:
		  	case AIR_Action_ShowRaw:
		  	case AIR_Action_DeleteItem:
	   	case AIR_Action_PurgeType:
			case AIR_Action_AuditItem:
		  		$this->showAirElementMaintInfo();
		  		break;
			}
		$this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);

		/*
		 * The following variables are typically missing and result in empty
		 * values being passed. However, when diagnostics are reported rather
		 * than accepting the input data, this causes the original data to be
		 * pre-filled on the replay of the form rather than being lost.
		 */
		$this->anchor->setDlgVar('Ele_EleName',		$this->myTxnSpec['EleName']);
		$this->anchor->setDlgVar('Ele_ChgComments',	$this->myTxnSpec['EleChgComments']);
		}

	/***************************************************************************
	 * showAirElementBaseInfo2()
	 *
	 * Create SMARTY template trigger data to show diagnostics and/or routine
	 * transaction results.
	 *******/
	function showAirElementBaseInfo2()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		/*
		 * Set display options for key control values that should not be modified as
		 * part of detail item maintenance. These should be changed using transaction
		 * mechanisms that are specifically geared toward those changes. Changing both
		 * the contextual model and the detail model within the same transaction mechanism
		 * is not allowed.
		 */
		$showAsArray		= false;
		$showAsModifiable = false;
		$showAsReadOnly	= true;

		/*
		 * Post context and element property information to the display management array
		 */
		$dlgContext	= $this->procContext->getContextId();

		$this->setSelectionListInfo('Model Context', 'Context',  AIR_EleType_Context,  $dlgContext,                  $showAsArray, $showAsReadOnly);
		$this->setSelectionListInfo('Element Type',  'EleType',  AIR_EleType_EleType,  $this->myTxnSpec['EleType'],  $showAsArray, $showAsReadOnly);
		if ($this->procContext->getSessionData('txnContextEleType') == $this->myTxnSpec['EleType'])
			{
  			$eleTypeClass = $this->procContext->getSessionData('txnContextEleTypeClass');
			}
		else
			{
			if (! empty($this->myTxnSpec['EleType']))
				{
	  			$eleTypeDoc = & $this->anchor->Repository->getElementRef($this->myTxnSpec['EleType']);
  				if (is_object($eleTypeDoc))
  					{
		  			$eleTypeClass = $eleTypeDoc->getElementData('EleClass');
					$this->setSelectionListInfo('Type Class',		'EleClass', AIR_EleType_EleClass, $eleTypeClass, $showAsArray, $showAsReadOnly);
		  			}
		  		}
  			}
		$this->setDisplayRule();

		if (($this->myTxnSpec['TxnOper'] == AIR_Action_Add)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Load)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_Modify)
		 || ($this->myTxnSpec['TxnOper'] == AIR_Action_ModifyRaw))
			{
			$showAsArray		= true;
			$showAsModifiable	= true;
			$showAsReadOnly	= false;
			}
		else
			{
			$showAsArray		= false;
			$showAsModifiable = false;
			$showAsReadOnly	= true;
			}

  		if (($this->myContextAction == AIR_Action_ShowItem)
  		 || ($this->myContextAction == AIR_Action_ShowRaw))
			{
			$dlgDataCaptureOper	= false;
			}
		else
		 	{
			$dlgDataCaptureOper	= true;
			}
		$this->anchor->setDlgVar('air_dataCaptureOper', $dlgDataCaptureOper);

		switch ($this->myContextAction)
		  	{
		  	case AIR_Action_Modify:
		  	case AIR_Action_ModifyRaw:
		  	case AIR_Action_ShowItem:
		  	case AIR_Action_ShowRaw:
		  	case AIR_Action_DeleteItem:
	   	case AIR_Action_PurgeType:
	   	case AIR_Action_AuditItem:
		  		$this->showAirElementMaintInfo();
		  		break;
			}
		$this->anchor->setDlgVar('dlgAction', $this->myTxnSpec['TxnOper']);

		/*
		 * The following variables are typically missing and result in empty
		 * values being passed. However, when diagnostics are reported rather
		 * than accepting the input data, this causes the original data to be
		 * pre-filled on the replay of the form rather than being lost.
		 */
		$this->setTextDisplay('Element Name',		'dlgEleName',			$this->myTxnSpec['EleName'],			$showAsReadOnly);
		if ($dlgDataCaptureOper)
			{
			$this->setTextboxDisplay('Update Comments',	'dlgEleChgComments', $this->myTxnSpec['EleChgComments'], false);
			}
		}

	/***************************************************************************
	 * showAirElementMaintInfo()
	 *
	 * Create SMARTY template trigger data to show diagnostics and/or routine
	 * transaction results.
	 *******/
	function showAirElementMaintInfo()
		{
		if (($this->anchor != NULL) && ($this->anchor->trace()))
			{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

		if (array_key_exists('EleCreateEntity', $this->myTxnSpec))
			{
			$authorName = '';
			$authorId	= $this->myTxnSpec['EleCreateEntity'];
 			if (! empty($authorId))
 				{
		 		$authorName = $this->anchor->Repository->getElementName($authorId);
		 		}
 			$modifierName 	= '';
			$modifierId		= $this->myTxnSpec['EleLastChgEntity'];
 			if (! empty($modifierId))
 				{
		 		$modifierName = $this->anchor->Repository->getElementName($modifierId);
		 		}

			$this->anchor->setDlgVar('Ele_CreateEntity', $authorName);
			$this->anchor->setDlgVar('Ele_CreateDt',		$this->myTxnSpec['EleCreateDt']);
			$this->anchor->setDlgVar('Ele_ChgEntity',		$modifierName);
			$this->anchor->setDlgVar('Ele_ChgDt',			$this->myTxnSpec['EleLastChgDt']);
			switch ($this->myTxnSpec['EleLastChgType'])
				{
				case AIR_EleChgType_Insert: $chgType = 'Add';		break;
				case AIR_EleChgType_Modify: $chgType = 'Update';	break;
				case AIR_EleChgType_Delete: $chgType = 'Delete';	break;
				case AIR_EleChgType_Null:
				default:							 $chgType = 'huh?';		break;
				}
			$this->anchor->setDlgVar('Ele_ChgType', $chgType);
			$this->anchor->setDlgVar('Ele_ChgComments',	$this->myTxnSpec['EleLastChgComments']);
			}
		$this->anchor->setDlgVar('air_showSaveCommand', false);
		}

	}

 ?>