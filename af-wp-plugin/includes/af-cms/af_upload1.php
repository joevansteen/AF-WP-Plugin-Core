<?php
/*
 * af_upload1 script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-13 JVS Begin test of new standalone PHP environment script
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'af_upload1';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');


	$content = '';

	$anchor->dlgStepResponse->title = 'Element Upload';
	$content .= '<form ';
	$content .= 'enctype="multipart/form-data" ';
	$content .= 'action="'.$anchor->getActionUrlBase().'dialog=';
	$content .= $anchor->dlgStepResponse->pageDialog;
	$content .= '" method=post>';
	$content .= '<input type="hidden" name="MAX_FILE_SIZE" value="100000000" />';
	$content .= 	'<center><table>';
	$content .= '<tr>';
	$content .= '<td colspan="2" align="center">' . '<b><em>File(s) to upload</em></b>' . '</td>';
	$content .= '</tr>';
	foreach($filelist as $fileKey => $fileEntry)
		{
		$content .= '<tr>';
		$content .= '<td align="right">' . $fileEntry['fileDescr'] . '</td>';
		$content .= '<td align="left">';
		$content .= '<input name="';
		$content .= $fileEntry['fileIdent'];
		$content .= '" type="file" />';
		$content .= '</td>';
		$content .= '</tr>';
		}
	$content .= '<tr>';
	$content .= '<td colspan="2" align="center">';
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Okay.'" />';
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Quit.'" />';
	$content .= '<input type="reset" value="'.AIR_Action_Reset.'" />';
	$content .= '</td>';
	$content .= '</tr>';
	$content .= '</table></center></form>';

	$anchor->dlgStepResponse->setContent($content);
?>