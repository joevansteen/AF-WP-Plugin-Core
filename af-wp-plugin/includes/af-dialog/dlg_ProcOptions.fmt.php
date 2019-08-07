<?php
/*
 * dlg_ProcOptions.fmt script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-31 JVS Integration with new standalone PHP environment scripts.
 *								This is a replacement for tiki-air_ProcOptions.tpl and
 *								was bootstrapped from airEleMaint.fmt
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirProcOptionsPanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirProcOptionsPanel extends C_HtmlPanel {
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
		$air_showContext			= $this->anchor->getDlgVar('air_showContext');
		$air_showContextArray	= $this->anchor->getDlgVar('air_showContextArray');
		$air_Context				= $this->anchor->getDlgVar('air_Context');
		$air_selectedContext		= $this->anchor->getDlgVar('air_selectedContext');
		$air_showEleTypeArray	= $this->anchor->getDlgVar('air_showEleTypeArray');
		$air_listItemArray		= $this->anchor->getDlgVar('air_listItemArray');
		$air_SelectMultiple		= $this->anchor->getDlgVar('air_SelectMultiple');
//		$air_selectedEleType		= $this->anchor->getDlgVar('air_selectedEleType');
		$air_EleType				= $this->anchor->getDlgVar('air_EleType');
		$dlgAction					= $this->anchor->getDlgVar('dlgAction');
		$panelTitle					= $this->anchor->getDlgVar('panelTitle');
		$panelSubtitle				= $this->anchor->getDlgVar('panelSubtitle');
		$panelItemTitle			= $this->anchor->getDlgVar('panelItemTitle');
		$panelItemSubtitle		= $this->anchor->getDlgVar('panelItemSubtitle');
		$air_selectedEleType		= $this->anchor->getDlgVar('air_selectedEleType');
		$air_showDiagnostics		= $this->anchor->getDlgVar('air_showDiagnostics');
		$resultDiag					= $this->anchor->getDlgVar('resultDiag');

		$Ele_CreateEntity			= $this->anchor->getDlgVar('Ele_CreateEntity');
		$Ele_CreateDt				= $this->anchor->getDlgVar('Ele_CreateDt');
		$Ele_ChgType				= $this->anchor->getDlgVar('Ele_ChgType');
		$Ele_ChgEntity				= $this->anchor->getDlgVar('Ele_ChgEntity');
		$Ele_ChgDt					= $this->anchor->getDlgVar('Ele_ChgDt');
		$Ele_ChgComments			= $this->anchor->getDlgVar('Ele_ChgComments');

		$air_ItemHeader			= $this->anchor->getDlgVar('air_ItemHeader');
//		$air_Dialog					= $this->anchor->getDlgVar('air_Dialog');
		$air_ItemFooter			= $this->anchor->getDlgVar('air_ItemFooter');

		/*
		 * Construct and file our personality objects into the panel outline
		 */
		$this->setHiddenElement('dlgClientDlgID',	'Client2randomvalue');
		$this->setHiddenElement('dlgCorrID',		$this->anchor->sessionDoc->getCorrId());
		$this->setHiddenElement('dlgDlgID',			$this->anchor->sessionDoc->getDialogId());
		$this->setHiddenElement('dlgMode',			$this->anchor->sessionDoc->getModeId());
		$this->setHiddenElement('dlgAuth',			$this->anchor->sessionDoc->getAuthId());
		$this->setHiddenElement('dlgOptions',		'true');

		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleH1, null, $panelTitle);
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
		if ($panelItemTitle == '')
			{
			$panelItemTitle == $air_EleType[$air_selectedEleType];
			}
		$myTitleData = new C_AirHtmlElement($this);
		$myTitleData->initialize(htmlEleSpan, null, $panelItemTitle);
		$myTitleData->setAttribute(htmlAttrClass, 'ItemTitle');
		$myTitleData->setAttribute(htmlAttrAlign, 'left');
		if (! empty($panelItemSubtitle))
			{
			$work		= & $myTitleData->createChild(htmlEleSpan);
			$work->setAttribute(htmlAttrClass, 'ItemSubtitle');
			$work		= & $work->createChild(htmlEleMini, '<br/>'.$panelItemSubtitle);
			}
		$pageItem['itemTitle']	= & $myTitleData;

		$pageItem['itemHeader']	= & $this->formatStdDialogArray($air_ItemHeader);

		$pageBody = new C_AirHtmlElement($this);
		$pageBody->initialize(htmlEleTable);

		if ($air_showContext)
			{
			$optTabRow		= & $pageBody->createChild(htmlEleTblRow);
			$optTabData		= & $optTabRow->createChild(htmlEleTblData, '&#160;');
									 $optTabData->setAttribute(htmlAttrAlign, 'right');
			$optTabData		= & $optTabRow->createChild(htmlEleTblData);
									 $optTabData->setAttribute(htmlAttrAlign, 'left');
			$work				= & $optTabData->createChild(htmlEleParagraph);
			$textInfo		= 'The context defines your current <em>viewpoint</em> ';
			$textInfo	  .= ' with respect to the repository content.';
			$textInfo	  .= ' It causes filtering to be applied to what you see';
			$textInfo	  .= ' as element types, properties, associations and related items.';
			$work->setContent($this->htmlLinewrap($textInfo));

			$optTabRow		= & $pageBody->createChild(htmlEleTblRow);
			$optTabData		= & $optTabRow->createChild(htmlEleTblData, 'Context');
									 $optTabData->setAttribute(htmlAttrAlign, 'right');
			$optTabData		= & $optTabRow->createChild(htmlEleTblData);
									 $optTabData->setAttribute(htmlAttrAlign, 'left');
			if ($air_showContextArray)
				{
				$work			= & $optTabData->createChild(htmlEleSelect);
				$work->setAttribute(htmlAttrName, 'dlgContext');
				$this->anchor->sessionDoc->putDialogVarTracker('dlgContext', 'Item'); // Catalog the dialog form variables
				foreach ($air_Context as $key => $value)
					{
					$item		= & $work->createChild(htmlEleOption, $value);
					$item->setAttribute(htmlAttrValue, $key);
					if ($key == $air_selectedContext)
						{
						$item->setAttribute(htmlAttrSelected);
						}
					}
				}
			else
				{
				$work		= & $optTabData->createChild(htmlEleInput);
				$work->setAttribute(htmlAttrType, 'text');
				$work->setAttribute(htmlAttrValue, $air_Context[$air_selectedContext]);
				$work->setAttribute(htmlAttrSize, '64');
				$work->setAttribute(htmlAttrReadonly);
				$this->setHiddenElement('dlgContext',			$air_selectedContext);
				}

			}
		else
			{
			$this->setHiddenElement('dlgContext',			$this->anchor->sessionDoc->getContextId());
			}

//		echo 'ProcOptFormat '. __LINE__ . ' airContext = '.$air_Context.'<br/>';

		$optTabRow		= & $pageBody->createChild(htmlEleTblRow);
		$optTabData	= & $optTabRow->createChild(htmlEleTblData, 'Options');
								 $optTabData->setAttribute(htmlAttrAlign, 'right');
		$optTabData	= & $optTabRow->createChild(htmlEleTblData);
								 $optTabData->setAttribute(htmlAttrAlign, 'left');
		$workset			= & $optTabData->createChild(htmlEleFormCtlGrp);
		$itemTable		= & $workset->createChild(htmlEleTable);
		$columns			= 1;

		foreach ($air_listItemArray as $key => $value)
			{
			if ($value['type'] != AIR_ContentType_Boolean)
				{
				$columns			= 2;
				break;
				}
			}

		foreach ($air_listItemArray as $key => $value)
			{
			$itemTabRow	= & $itemTable->createChild(htmlEleTblRow);
			if ($value['type'] == AIR_ContentType_Boolean)
				{
				$itemTabData = & $itemTabRow->createChild(htmlEleTblData);
									  $itemTabData->setAttribute(htmlAttrColSpan, $columns);
									  $itemTabData->setAttribute(htmlAttrAlign, 'left');
				$work		= & $itemTabData->createChild(htmlEleInput);

				if ($air_SelectMultiple)
					{
					$work->setAttribute(htmlAttrType, 'checkbox');
					$work->setAttribute(htmlAttrName, $key);
			 		if ((strpos($key, 'dlgChoice:') == 0)
					 && (strpos($key, 'dlgChoice:') !== false))
						{
						/*
						 * Checkbox values recorded in HTML with unique 'dlgChoice:xxxxx' variable
						 * names will be re-coded and pased in the original decode message as a
						 * collection of 'dlgObject' elements.
						 */
						$this->anchor->sessionDoc->putDialogVarTracker('dlgObject', 'Collection'); // Catalog the dialog form variables
						}
					else
						{
						$this->anchor->sessionDoc->putDialogVarTracker($key, 'Item'); // Catalog the dialog form variables
						}
					}
				else
					{
					$work->setAttribute(htmlAttrType, 'radio');
					$work->setAttribute(htmlAttrName, 'dlgAction');
					$work->setAttribute(htmlAttrValue, $key);
					$this->anchor->sessionDoc->putDialogVarTracker('dlgAction', 'Item'); // Catalog the dialog form variables
					}
				if ($value['on'])
					{
					$work->setAttribute(htmlAttrChecked);
					}
				$work->setContent($value['content']);

//					$work		= & $itemTabData->createChild(htmlEleBreak)
				}
			else
				{
				$itemTabData = & $itemTabRow->createChild(htmlEleTblData);
									  $itemTabData->setAttribute(htmlAttrAlign, 'right');
									  $itemTabData->setContent($value['content']);
				$itemTabData = & $itemTabRow->createChild(htmlEleTblData);
									  $itemTabData->setAttribute(htmlAttrAlign, 'left');
				switch ($value['type'])
					{
					case AIR_ContentType_IntText:
					case AIR_ContentType_Integer:
						$work		= & $itemTabData->createChild(htmlEleInput);
						$work->setAttribute(htmlAttrType, 'text');
						$work->setAttribute(htmlAttrName, $key);
						$this->anchor->sessionDoc->putDialogVarTracker($key, 'Item'); // Catalog the dialog form variables
//						$work->setAttribute(htmlAttrMaxLength, $value['itemMaxSize']);
//						$work->setAttribute(htmlAttrSize, $value['itemSize']);
						$work->setAttribute(htmlAttrValue, $value['on']);
						break;
					}
				}
			}

		$pageItem['itemBody']	= & $pageBody;

		$pageItem['itemFooter']	= & $this->formatStdDialogArray($air_ItemFooter);

		$itemArray[]				= $pageItem;

		$this->panelItems = $itemArray;
		}

	} // end of class

 ?>