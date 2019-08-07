<?php
/*
 * dlg_AirAudit.fmt script Copyright (c) 2008 Architected Futures, LLC
 *
 * V1.8 2008-MAR-30 JVS Bootstrap from dlg_EleMaint
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'C_AirAirAuditPanel';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

class C_AirAirAuditPanel extends C_HtmlPanel {
	/***************************************************************************
	 * Constructor
	 *
	 * Initialize the local variable store and creates a local
	 * reference to the AIR_anchor object for later use in
	 * detail function processing. (Be careful with code here
	 * to ensure that we are really talking to the right object.)
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
//		$air_SelectMultiple		= $this->anchor->getDlgVar('air_SelectMultiple');
		$air_EleType				= $this->anchor->getDlgVar('air_EleType');
		$dlgPanelType				= $this->anchor->getDlgVar('dlgPanelType');
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
		$air_Dialog					= $this->anchor->getDlgVar('air_Dialog');
		$air_ItemFooter			= $this->anchor->getDlgVar('air_ItemFooter');

		/*
		 * Construct and file our personality objects into the panel outline
		 */
//		$this->setHiddenElement('dlgContext',		$this->anchor->sessionDoc->getContextId());
		$this->setHiddenElement('dlgPanelType',	$dlgPanelType);
		$this->setHiddenElement('dlgClientDlgID',	'Client2randomvalue');
		$this->setHiddenElement('dlgCorrID',		$this->anchor->sessionDoc->getCorrId());
		$this->setHiddenElement('dlgDlgID',			$this->anchor->sessionDoc->getDialogId());
		$this->setHiddenElement('dlgMode',			$this->anchor->sessionDoc->getModeId());
		$this->setHiddenElement('dlgAuth',			$this->anchor->sessionDoc->getAuthId());


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
			$work		= & $work->createChild(htmlEleMini, $panelItemSubtitle);
			}
		if (! empty($Ele_CreateEntity))
			{
			$annotationText	= 	'Created by: '.$Ele_CreateEntity.' on: '.date("D M j, Y, g:i:s a", $this->makeTimestamp($Ele_CreateDt));
			if ($Ele_ChgType != 'Add')
				{
				$annotationText	.= 	'<br/>'.$Ele_ChgType.' by: '.$Ele_ChgEntity.' on: '.date("D M j, Y, g:i:s a", $this->makeTimestamp($Ele_ChgDt));
				}
			if (! empty($Ele_ChgComments))
				{
				$annotationText	.= 	'<br/>Annotation: '.$Ele_ChgComments;
				}
			$work		= & $myTitleData->createChild(htmlEleSpan);
			$work->setAttribute(htmlAttrClass, 'ItemTitleAnnotation');
			$work		= & $work->createChild(htmlEleMini, $annotationText);
			/*			{|tiki_short_datetime}<br/> */
//			if ($Ele_ChgType != 'Add')
//				{
//				$work		= & $myTitleData->createChild(htmlEleSpan);
//				$work->setAttribute(htmlAttrClass, 'ItemTitleAnnotation');
//				$work		= & $work->createChild(htmlEleMini, $Ele_ChgType.' by: '.$Ele_ChgEntity.' on: '.$Ele_ChgDt);
//				}
//			if (! empty($Ele_ChgComments))
//				{
//				$work		= & $myTitleData->createChild(htmlEleSpan);
//				$work->setAttribute(htmlAttrClass, 'ItemTitleAnnotation');
//				$work		= & $work->createChild(htmlEleMini, 'Annotation: '.$Ele_ChgComments);
//				}
			}
		$pageItem['itemTitle']	= & $myTitleData;

		$pageItem['itemHeader']	= & $this->formatStdDialogArray($air_ItemHeader);
		$pageBody					= & $this->formatStdDialogArray($air_Dialog);
		$pageItem['itemBody']	= & $pageBody;

		$pageItem['itemFooter']	= & $this->formatStdDialogArray($air_ItemFooter);

		$itemArray[]				= $pageItem;

		$this->panelItems = $itemArray;
		}

	} // end of class
/***************************************
				<tr><td>{tr}Related Tasks{/tr}</td>
				    <td>
					<input type="checkbox" name="dlgTaskAddProp"  checked /> {$dlgAction} {tr}Properties{/tr} <br/>
					<input type="checkbox" name="dlgTaskAddAssoc" checked /> {$dlgAction} {tr}Associations{/tr} <br/>
					<input type="checkbox" name="dlgTaskAddComp" />          {$dlgAction} {tr}Components{/tr} <br/>
					<input type="checkbox" name="dlgTaskAddComment" />       {$dlgAction} {tr}Comments{/tr}
					</td></tr>

				{* ----------------------------------------------------------------------
					The code was used to try to debug two variation on data capture
					options. Neither proved successful as written. The attempts here
					are to provide other forms of multiple selection. In both cases,
					only a single selection was reported to the server containing the
					last item selected, and the multiple selection feature was not effective.

				<tr><td>{tr}HTML Test 1{/tr}</td>
				    <td>
						<SELECT NAME="toppings" MULTIPLE SIZE="3">
							<OPTION VALUE="mushrooms" selected />mushrooms
							<OPTION VALUE="greenpeppers" />green peppers
							<OPTION VALUE="onions" selected />onions
							<OPTION VALUE="tomatoes" />tomatoes
							<OPTION VALUE="olives" />olives
							</SELECT>
					</td></tr>

				<tr><td>{tr}HTML Test 2{/tr}</td>
				    <td>
						<input type="checkbox" name="SelectedDepts" value="one" />  Training <br/>
						<input type="checkbox" name="SelectedDepts" value="two" />  Marketing <br/>
						<input type="checkbox" name="SelectedDepts" value="three" />  Human Resources <br/>
						<input type="checkbox" name="SelectedDepts" value="four" />  Manufacturing <br/>
						<input type="checkbox" name="SelectedDepts" value="five" />  Sales <br/>
					</td></tr>
				   ---------------------------------------------------------------------- *}

	 ****************************************************************/


/*
 * Additional code to test PHP process flow under various arrangements.
 * This code is executed once per 'include' as the module is scanned
 * and all code not inside newly defined "function blocks" is executed.
 */
	if ($this->anchor->debugCoreFcns())
		{
		echo '<debug>'.__FILE__.'['.__LINE__.']'."*** $myDynamClass() include initialization concluded ***".'<br/></debug> ';
		}
 ?>