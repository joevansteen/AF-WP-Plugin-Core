<?php
/*
 * af_sqlexec2 script Copyright (c) 2005 Architected Futures, LLC
 *
 * V1.3 2005-OCT-21 JVS Begin test of new standalone PHP environment script
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V5   2019-APR-04 JVS EATSv5 required script include segment
 */

// Insure a correct execution context ...
$myProcClass = 'af_sqlrebuild';
$myDynamClass = $myProcClass;	
require(AF_AUTOMATED_SCRIPTS.'/af_script_header.php');

	$anchor->dlgStepResponse->title = 'SQL Exec Preview';
	$currentDir 	= '/temp/';
	$pathName 		= $currentDir.$target;
//	$fileContentX	= $anchor->getFileContent($pathName);

	$results = array();
	$more = true;
	$next = 1;
	while ($next < 13)
		{
		$query	= "";
		$nextResult = array();
		switch ($next)
			{
			case 1:
				$query	 .= "	DROP TABLE IF EXISTS `AIR_EleAssociations`;";
				$nextResult['action'] = 'DROP AIR_EleAssociations';
				break;

			case 2:
				$query .= "	CREATE TABLE IF NOT EXISTS `AIR_EleAssociations` (";
				$query .= "  `Air_Ele_Id` varchar(64) NOT NULL default '',";
				$query .= "  `Air_Assoc_Subject` varchar(64) NOT NULL default '',";
				$query .= "  `Air_Assoc_Predicate` varchar(64) NOT NULL default '',";
				$query .= "  `Air_Assoc_Object` varchar(64) NOT NULL default '',";
				$query .= "  `Air_Assoc_IObject` varchar(64) default NULL,";
				$query .= "  PRIMARY KEY  (`Air_Ele_Id`),";
				$query .= "  KEY `Air_Assoc_Subject` (`Air_Assoc_Subject`),";
				$query .= "  KEY `Air_Assoc_Predicate` (`Air_Assoc_Predicate`),";
				$query .= "  KEY `Air_Assoc_Object` (`Air_Assoc_Object`),";
				$query .= "  KEY `Air_Assoc_IObject` (`Air_Assoc_IObject`)";
				$query .= " ) TYPE=InnoDB COMMENT='AIR element associations table';";
				$nextResult['action'] = 'CREATE AIR_EleAssociations';
				break;

			case 3;
				$query .= "	DROP TABLE IF EXISTS `AIR_EleIndex`;";
				$nextResult['action'] = 'DROP AIR_EleIndex';
				break;

			case 4;

				$query .= "	CREATE TABLE IF NOT EXISTS `AIR_EleIndex` (";
				$query .= "	  `Air_Ele_Id` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_Ele_CurrRowStatus` char(1) NOT NULL default '0',";
				$query .= "	  `Air_Ele_CurrKeyDiscriminator` varchar(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_HiKeySerial` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_CreateDt` varchar(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_CreateEntity` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_Ele_ChgDt` varchar(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_ChgType` char(1) NOT NULL default 'I',";
				$query .= "	  `Air_Ele_ChgEntity` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_Ele_ChgComments` varchar(255) default NULL,";
				$query .= "	  `Air_Ele_ChgPubWorkflow` varchar(64) default NULL,";
				$query .= "	  `Air_Ele_ChgPendingStatus` char(1) NOT NULL default 'N',";
				$query .= "	  `Air_Ele_EffDtStart` varchar(20) default NULL,";
				$query .= "	  `Air_Ele_EffDtEnd` varchar(20) default NULL,";
				$query .= "	  `Air_Ele_CntElements` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_CntAssociations` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_CntProperties` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_CntRelationships` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_RefbyElements` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_RefByAssociations` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_RefByProperties` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_RefByRelationships` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_EleType` varchar(64) default NULL,";
				$query .= "	  `Air_Ele_EleName` varchar(255) NOT NULL default '',";
				$query .= "	  `Air_Ele_EleContentSize` bigint(20) NOT NULL default '0',";
				$query .= "	  PRIMARY KEY  (`Air_Ele_Id`),";
				$query .= "	  KEY `Air_Ele_EleType` (`Air_Ele_EleType`),";
				$query .= "	  KEY `Air_Ele_EleName` (`Air_Ele_EleName`)";
				$query .= "	) TYPE=InnoDB COMMENT='AIR element index';";
				$nextResult['action'] = 'CREATE AIR_EleIndex';
				break;

			case 5;
				$query .= "	DROP TABLE IF EXISTS `AIR_EleProperties`;";
				$nextResult['action'] = 'DROP AIR_EleProperties';
				break;

			case 6;
				$query .= "	CREATE TABLE IF NOT EXISTS `AIR_EleProperties` (";
				$query .= "	  `Air_Ele_Id` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_Prop_Subject` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_Prop_Predicate` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_Prop_Object` varchar(64) NOT NULL default '',";
				$query .= "	  PRIMARY KEY  (`Air_Ele_Id`),";
				$query .= "	  KEY `Air_Prop_Subject` (`Air_Prop_Subject`),";
				$query .= "	  KEY `Air_Prop_Predicate` (`Air_Prop_Predicate`),";
				$query .= "	  KEY `Air_Prop_Object` (`Air_Prop_Object`)";
				$query .= "	) TYPE=InnoDB COMMENT='AIR element properties table';";
				$nextResult['action'] = 'CREATE AIR_EleProperties';
				break;

			case 7;
				$query .= "	DROP TABLE IF EXISTS `AIR_EleRelationships`;";
				$nextResult['action'] = 'DROP AIR_EleRelationships';
				break;

			case 8;
				$query .= "	CREATE TABLE IF NOT EXISTS `AIR_EleRelationships` (";
				$query .= "	  `Air_Rel_Subject` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_Rel_Predicate` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_Rel_Object` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_Rel_RefCount` bigint(20) NOT NULL default '0',";
				$query .= "	  PRIMARY KEY  (`Air_Rel_Subject`,`Air_Rel_Predicate`,`Air_Rel_Object`),";
				$query .= "	  KEY `Air_Rel_Predicate` (`Air_Rel_Predicate`),";
				$query .= "	  KEY `Air_Rel_Object` (`Air_Rel_Object`)";
				$query .= "	) TYPE=InnoDB COMMENT='AIR element relationships table';";
				$nextResult['action'] = 'CREATE AIR_EleRelationships';
				break;

			case 9;
				$query .= "	DROP TABLE IF EXISTS `AIR_Elements`;";
				$nextResult['action'] = 'DROP AIR_Elements';
				break;

			case 10;
				$query .= "	CREATE TABLE IF NOT EXISTS `AIR_Elements` (";
				$query .= "	  `Air_Ele_Id` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_Ele_RowStatus` char(1) NOT NULL default '0',";
				$query .= "	  `Air_Ele_KeyDiscriminator` varchar(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_KeySerial` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_SerialFlag` char(1) NOT NULL default '0',";
				$query .= "	  `Air_Ele_EleContentSize` bigint(20) NOT NULL default '0',";
				$query .= "	  `Air_Ele_EleContent` longtext,";
				$query .= "	  PRIMARY KEY  (`Air_Ele_Id`,`Air_Ele_RowStatus`,`Air_Ele_KeyDiscriminator`,`Air_Ele_KeySerial`)";
				$query .= "	) TYPE=InnoDB COMMENT='AIR elements table';";
				$nextResult['action'] = 'CREATE AIR_Elements';
				break;

			case 11;
				$query .= "	DROP TABLE IF EXISTS `AIR_RelRules`;";
				$nextResult['action'] = 'DROP AIR_RelRules';
				break;

			case 12;
				$query .= "	CREATE TABLE IF NOT EXISTS `AIR_RelRules` (";
				$query .= "	  `Air_Ele_Id` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_RelRule_Subject` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_RelRule_Predicate` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_RelRule_PredOrd` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_RelRule_PredCard` varchar(64) NOT NULL default '',";
				$query .= "	  `Air_RelRule_PredMax` int(11) NOT NULL default '0',";
				$query .= "	  `Air_RelRule_Object` varchar(64) default NULL,";
				$query .= "	  `Air_RelRule_IObject` varchar(64) default NULL,";
				$query .= "	  `AIR_RelRule_Diag` varchar(64) default NULL,";
				$query .= "	  PRIMARY KEY  (`Air_RelRule_Subject`,`Air_RelRule_Predicate`)";
				$query .= "	) TYPE=InnoDB COMMENT='AIR Relationship Rules Table';";
				$nextResult['action'] = 'CREATE AIR_RelRules';

				$more = false;

				break;
			}

		$result 						= $anchor->db->query2($query);
		$nextResult['result']	= $result;
		$results[]					= $nextResult;
		$next++;
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
	$content .= '<td align="right">' . 'Type:' . '</td>';
	$content .= '<td align="left">' . filetype($pathName) . '</td>';
	$content .= '</tr>';

	$content .= '<tr>';
	$content .= '<td align="right">' . 'Permissions:' . '</td>';
	$content .= '<td align="left">' . decoct(fileperms($pathName)) . '</td>';
	$content .= '</tr>';

	$content .= '<tr>';
	$content .= '<td align="right">' . 'Size (Bytes):' . '</td>';
	$content .= '<td align="left">' . filesize($pathName) . '</td>';
	$content .= '</tr>';

	$content .= '<tr>';
	$content .= '<td align="right">' . 'Last Modified:' . '</td>';
	$content .= '<td align="right">' . date('j F Y H:i', filemtime($pathName)) . '</td>';
	$content .= '</tr>';

	$content .= '<tr>';
	$content .= '<td align="right">' . 'Last Accessed:' . '</td>';
	$content .= '<td align="right">' . date('j F Y H:i', fileatime($pathName)) . '</td>';
	$content .= '</tr>';

	$content .= '<tr>';
	$content .= '<td colspan="2" align="center">';
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
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Okay.'" />';
	$content .= '<input type="submit" name="request" value="'.AIR_Action_Edit.'" />';
	$content .= '</td>';
	$content .= '</tr>';
	$content .= '</table></center></form>';

	$anchor->dlgStepResponse->setContent($content);
?>