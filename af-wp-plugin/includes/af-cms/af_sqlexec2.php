<?php
/*
 * af_sqlexec2 script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-21 JVS Begin test of new standalone PHP environment script
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'af_sqlexec2';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	$anchor->dlgStepResponse->title = 'SQL Exec Results';
	$pathName 		= $_SESSION['sqlTarget'];
	$beginRow 		= $_SESSION['sqlStart'];
	$startRow 		= $_SESSION['sqlStart'];
	$results 		= array();
	$dataStream		= new C_AirTextStream($anchor);
	$dataStream->initialize($pathName);
	$cmdArray		= array();
	$cmdLimit		= 100;

	$fileContent 	= $dataStream->getNextFragment();
	$cmdContent		= '';
	while ($fileContent != null)
		{
		$cmdContent .= $fileContent;
		if ($dataStream->lastChar($fileContent, ';'))
			{
			$cmdArray[]		=	$cmdContent;
			$cmdContent		= '';
			}

		$fileContent 	= $dataStream->getNextFragment();
		}

	$cmdCount		= count($cmdArray);
	$rowsProcessed	= 0;

	if ($startRow < $cmdCount)
		{
		for ($i = 0; $i < $cmdLimit; $i++)
			{
			$cmdIndex	= $startRow + $i;
			if ($cmdIndex < $cmdCount)
				{
				$cmdContent = $cmdArray[$cmdIndex];

				$result 						= $anchor->db->query2($cmdContent);
				if (! $result)
					{
					$nextResult['item'] 		= $cmdIndex;
					$nextResult['action'] 	= $cmdContent;
					$nextResult['result']	= $result;
					$results[]					= $nextResult;
					}
				$rowsProcessed++;
				}
			}
		$startRow += $rowsProcessed;
		$_SESSION['sqlStart']	= $startRow;
		}

	$content = '';

	$content .= '<form ';
	$content .= 'action="'.$anchor->getActionUrlBase().'dialog=';
	$content .= $anchor->dlgStepResponse->pageDialog;
	$content .= '&target=' . $target . '" method=post>';


	$content .= '<center><table>';

	$content .= '<tr>';
	$content .= '<td><center><table><tr>';
	$content .= '<td colspan="2" align="center"><b><em>';
	$content .= 'Please review the following script carefully and ensure that it is what you want to do.';
	$content .= '</em></b></td>';
	$content .= '</tr>';

	$content .= '<tr>';
	$content .= '<td><center><table><tr>';
	$content .= '<td colspan="2" align="center"><b><em>' . $target . '</em></b></td>';
	$content .= '</tr>';

	foreach ($results as $result)
		{
		$content .= '<tr>';
		$content .= '<td align="right">' . $result['action'] . ' = </td>';
		$content .= '<td align="left">' . $result['result'] . '</td>';
		$content .= '</tr>';
		}

	$content .= '<tr>';
	$content .= '<td align="right">' . 'File Commands:' . '</td>';
	$content .= '<td align="left">' . $cmdCount . '</td>';
	$content .= '</tr>';

	$content .= '<tr>';
	$content .= '<td align="right">' . 'Start:' . '</td>';
	$content .= '<td align="left">' . $beginRow . '</td>';
	$content .= '</tr>';

	$content .= '<tr>';
	$content .= '<td align="right">' . 'Processed:' . '</td>';
	$content .= '<td align="left">' . $rowsProcessed . '</td>';
	$content .= '</tr>';

	if ($startRow < $cmdCount)
		{
		$content .= '<tr>';
		$content .= '<td align="right">' . 'Next Begin:' . '</td>';
		$content .= '<td align="right">' . $startRow . '</td>';
		$content .= '</tr>';
		}

	$content .= '<tr>';
	$content .= '<td colspan="2" align="center">';
	if ($startRow < $cmdCount)
		{
		$content .= '<input type="submit" name="request" value="'.AIR_Action_Next.'" />';
		}
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Okay.'" />';
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Edit.'" />';
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Quit.'" />';
	$content .= '</td>';
	$content .= '</tr></table></center></td>';
	$content .= '</tr>';

	$content .= '<tr><td align="left">';
	$content .= '<dl>';
	$content .= '<dt>Content</dt>';
	$content .= '<dd>';

//	if (($fileEntry['fileType'] == 'text/xml')
//	 || ($fileEntry['fileType'] == 'text/html')) // Could be dangerous in production, echo causes interpreted replay of html script
 		{
		$content .= htmlspecialchars($fileContent, ENT_QUOTES);
		}

	$content .= '</dd>';
	$content .= '</dl></td></tr>';

	$content .= '<tr>';
	$content .= '<td align="center">';
	if ($startRow < $cmdCount)
		{
		$content .= '<input type="submit" name="request" value="'.AIR_Action_Next.'" />';
		}
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Okay.'" />';
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Edit.'" />';
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Quit.'" />';
	$content .= '</td>';
	$content .= '</tr>';
	$content .= '</table></center></form>';

	$anchor->dlgStepResponse->setContent($content);
?>