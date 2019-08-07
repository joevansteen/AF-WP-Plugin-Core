<?php
/*
 * C_AF_AirDatabase script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.2 2005-SEP-14 JVS Code reshaping to utilize data (table) driven logic
 *                      to define data elements managed as part of
 *                      individual element type processing.
 * V1.3 2005-OCT-18 JVS Name change from air-anchor_sql to af_database
 *                      as part of extraction from TikiWiki framework.
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V1.8 2008-APR-06 JVS C_AF_AirDatabase refactored from af_database
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_AirDatabase';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_AirDatabase
	 *
	 * Defines the logical database layer for the AIR database tables. In the
	 * V1.0 through V1.2 code, this layer was positioned on top of the TikiWiki
	 * database scheme, which in turn was build on the PHP ADO library. In V1.3
	 * this code has been repositioned to sit on top of the C_AF_DbWrapper
	 * class. This removes us from direct dependence on TikiWiki and ADO.
	 *
	 * This class provides a series of logical methods for perfroming database
	 * operations against the various views of the AIR database. Operational
	 * code deals with the database either through various standard objects,
	 * or by use of the methods provided here.
	 ***************************************************************************/

class C_AF_AirDatabase extends C_AF_DbWrapper {

   // Table names
   $AIR_ELEMENTS =				$GLOBALS['AF_TABLESET'].'air_elements');
   $AIR_INDEX =				 	$GLOBALS['AF_TABLESET'].'air_eleindex');
   $AIR_PROPERTIES =			$GLOBALS['AF_TABLESET'].'air_eleproperties');
   $AIR_ASSOCIATIONS =		$GLOBALS['AF_TABLESET'].'air_eleassociations');
   $AIR_RELATIONSHIPS =	$GLOBALS['AF_TABLESET'].'air_elerelationships');
   $AIR_RULES =					$GLOBALS['AF_TABLESET'].'air_relrules');
   $AIR_USERS =					$GLOBALS['AF_TABLESET'].'user');

	$MIN_AIR_ID_SIZE	= 40;
	private $repository = NULL; // The C_AF_Repository manager for this database.

	/*********************************************************
	 * Constructor
	 *******/
	function __construct(& $repository)
		{
		// Propogate the construction process
		parent::__construct( $repository->anchor );

		if (( $GLOBALS['AF_INSTANCE'] != NULL) && ($GLOBALS['AF_INSTANCE']->trace()))
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}

	 	if (($repository == NULL)
	 	 || (!($repository instanceof C_AF_AirRepository)))
		 	{
		 	throw new Exception('Invalid instantiation');
		 	}

		$this->repository = $repository;		
		
 		$this->connect('localhost', DB_NAME, DB_USER, DB_PASSWORD);
		}

	/***************************************************************************
	 * terminate
	 *******/
	function terminate()
	 	{
		parent::terminate();
		}

	// ********************************************************
	// 		Database access code by 'class' of element

	//
	// 							E L E M E N T S
	//

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// AIR_EleIndex query functions:
	// get_AllElements()				select all elements
	// get_currAirElement()			select a single 'current' item by primary key

	/*********************************************************
	 * insert_AirElementsItem()
	 *******/
	function insert_AirElementsItem($colArray)
		{
		$retCode 						= true;
		$Air_Ele_Id						= $colArray['Air_Ele_Id'];
		$Air_Ele_RowStatus			= $colArray['Air_Ele_RowStatus'];
		$Air_Ele_KeyDiscriminator	= $colArray['Air_Ele_KeyDiscriminator'];
		$Air_Ele_KeySerial			= $colArray['Air_Ele_KeySerial'];
		$Air_Ele_SerialFlag			= $colArray['Air_Ele_SerialFlag'];
		$Air_Ele_EleContentSize		= $colArray['Air_Ele_EleContentSize'];
		$Air_Ele_EleContent			= $GLOBALS['AF_INSTANCE']->prepTextForSql($colArray['Air_Ele_EleContent']);

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "insert into air_elements"
					."	set	Air_Ele_Id						= '".$Air_Ele_Id."',"
					."			Air_Ele_RowStatus				= '".$Air_Ele_RowStatus."',"
					."			Air_Ele_KeyDiscriminator	= '".$Air_Ele_KeyDiscriminator."',"
					."			Air_Ele_KeySerial				= '".$Air_Ele_KeySerial."',"
					."			Air_Ele_SerialFlag			= '".$Air_Ele_SerialFlag."',"
					."			Air_Ele_EleContentSize		= '".$Air_Ele_EleContentSize."',"
					."			Air_Ele_EleContent			= '".$Air_Ele_EleContent."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * replace_AirElementsItem()
	 *******/
	function replace_AirElementsItem($colArray)
		{
		$retCode 						= true;
		$Air_Ele_Id						= $colArray['Air_Ele_Id'];
		$Air_Ele_RowStatus			= $colArray['Air_Ele_RowStatus'];
		$Air_Ele_KeyDiscriminator	= $colArray['Air_Ele_KeyDiscriminator'];
		$Air_Ele_KeySerial			= $colArray['Air_Ele_KeySerial'];
		$Air_Ele_SerialFlag			= $colArray['Air_Ele_SerialFlag'];
		$Air_Ele_EleContentSize		= $colArray['Air_Ele_EleContentSize'];
		$Air_Ele_EleContent			= $GLOBALS['AF_INSTANCE']->prepTextForSql($colArray['Air_Ele_EleContent']);

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "update air_elements"
					."	set	Air_Ele_EleContentSize		= '".$Air_Ele_EleContentSize."',"
					."			Air_Ele_EleContent			= '".$Air_Ele_EleContent."'"
					."	where	Air_Ele_Id						= '".$Air_Ele_Id."'"
					."	  and	Air_Ele_RowStatus				= '".$Air_Ele_RowStatus."'"
					."	  and	Air_Ele_KeyDiscriminator	= '".$Air_Ele_KeyDiscriminator."'"
					."	  and	Air_Ele_KeySerial				= '".$Air_Ele_KeySerial."'"
					."	  and	Air_Ele_SerialFlag			= '".$Air_Ele_SerialFlag."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirElementsItem()
	 *
	 * Note. This is a complete purge of everything in the table
	 * under the given ID value.
	 *******/
	function purge_AirElementsItem($Air_Ele_Id)
		{
		$retCode = true;
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "delete from air_elements"
					."	where	Air_Ele_Id						= '".$Air_Ele_Id."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirElementsSet()
	 *******/
	function purge_AirElementsSet($Air_Ele_Array)
		{
		if (count($Air_Ele_Array > 0))
			{
			$retCode = true;
			$first	= true;
			if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
			 	{
				$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
				}
			$sqlStmt = "delete from air_elements";

			foreach ($Air_Ele_Array as $EleIdent)
				{
				if ($first)
					{
					$sqlStmt .= "	where	Air_Ele_Id = '".$EleIdent."'";
					$first = false;
					}
				else
					{
					$sqlStmt .= " or Air_Ele_Id = '".$EleIdent."'";
					}
				}

			$result = $this->query2($sqlStmt);
			$retCode = $this->successful();
			}
		else
			{
			$retCode = false;
			}

		return($retCode);
		}

	/*********************************************************
	 * get_currAirElement()
	 *
	 * Obtain the 'current' version of an AIR_Element from the
	 * database. Only active 'current' versions of the element
	 * will be returned. No change history or pending versions
	 * will be included.
	 *
	 * This function may result in multiple rows of result data
	 * if a multi-row content exists in the dtabase for the
	 * current element. For the initial POC it is coded to
	 * return the first row. Later the limit statement should
	 * be activated to return rows in blocks as needed.
	 *******/
	function get_currAirElement($eleId, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_elements"
					."	where Air_Ele_Id = '".$eleId."'"
					." and Air_Ele_RowStatus = '".AIR_EleState_Current."'"
					."	and Air_Ele_KeyDiscriminator = '0'"
					."	order by Air_Ele_KeySerial asc"
					." limit ".$start.",".$count;
		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * getCount_AirElements()
	 *
	 * Obtain the count of the number of items in AIR_Elements.
	 *******/
	function getCount_AirElements()
		{
		$sqlStmt 		= "select count(*) as TotalRows from air_elements";
		$dbCountResult = $this->query1($sqlStmt);
		$dbCountArray 	= $dbCountResult[0];
		$ret				= $dbCountArray['TotalRows'];
		return($ret);
		}

	/*********************************************************
	 * get_AllElements()
	 *
	 * Select all elements
	 *******/
	function get_AllElements($count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_elements"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	//
	// 				E L E M E N T   I N D E X
	//

	/*********************************************************
	 * insert_AirEleIndexItem()
	 *******/
	function insert_AirEleIndexItem($colArray)
		{
		$retCode 							= true;
		$Air_Ele_Id							= $colArray['Air_Ele_Id'];
		$Air_Ele_CurrRowStatus			= $colArray['Air_Ele_CurrRowStatus'];
		$Air_Ele_CurrKeyDiscriminator = $colArray['Air_Ele_CurrKeyDiscriminator'];
		$Air_Ele_HiKeySerial				= $colArray['Air_Ele_HiKeySerial'];
		$Air_Ele_CreateDt					= $colArray['Air_Ele_CreateDt'];
		$Air_Ele_CreateEntity			= $colArray['Air_Ele_CreateEntity'];
		$Air_Ele_ChgDt						= $colArray['Air_Ele_ChgDt'];
		$Air_Ele_ChgType					= $colArray['Air_Ele_ChgType'];
		$Air_Ele_ChgEntity				= $colArray['Air_Ele_ChgEntity'];
		$Air_Ele_ChgComments				= $GLOBALS['AF_INSTANCE']->prepTextForSql($colArray['Air_Ele_ChgComments']);
		$Air_Ele_ChgPubWorkflow			= $colArray['Air_Ele_ChgPubWorkflow'];
		$Air_Ele_ChgPendingStatus		= $colArray['Air_Ele_ChgPendingStatus'];
		$Air_Ele_EffDtStart				= $colArray['Air_Ele_EffDtStart'];
		$Air_Ele_EffDtEnd					= $colArray['Air_Ele_EffDtEnd'];
		$Air_Ele_CntElements				= $colArray['Air_Ele_CntElements'];
		$Air_Ele_CntAssociations		= $colArray['Air_Ele_CntAssociations'];
		$Air_Ele_CntProperties			= $colArray['Air_Ele_CntProperties'];
		$Air_Ele_CntRelationships		= $colArray['Air_Ele_CntRelationships'];
		$Air_Ele_RefbyElements			= $colArray['Air_Ele_RefbyElements'];
		$Air_Ele_RefByAssociations		= $colArray['Air_Ele_RefByAssociations'];
		$Air_Ele_RefByProperties		= $colArray['Air_Ele_RefByProperties'];
		$Air_Ele_RefByRelationships	= $colArray['Air_Ele_RefByRelationships'];
		$Air_Ele_EleType					= $colArray['Air_Ele_EleType'];
		$Air_Ele_EleName					= $GLOBALS['AF_INSTANCE']->prepTextForSql($colArray['Air_Ele_EleName']);
		$Air_Ele_EleContentSize			= $colArray['Air_Ele_EleContentSize'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "insert into air_eleindex"
					."	set	Air_Ele_Id							= '".$Air_Ele_Id."',"
					."			Air_Ele_CurrRowStatus			= '".$Air_Ele_CurrRowStatus."',"
					."			Air_Ele_CurrKeyDiscriminator 	= '".$Air_Ele_CurrKeyDiscriminator."',"
					."			Air_Ele_HiKeySerial				= '".$Air_Ele_HiKeySerial."',"
					."			Air_Ele_CreateDt					= '".$Air_Ele_CreateDt."',"
					."			Air_Ele_CreateEntity				= '".$Air_Ele_CreateEntity."',"
					."			Air_Ele_ChgDt						= '".$Air_Ele_ChgDt."',"
					."			Air_Ele_ChgType					= '".$Air_Ele_ChgType."',"
					."			Air_Ele_ChgEntity					= '".$Air_Ele_ChgEntity."',"
					."			Air_Ele_ChgComments				= '".$Air_Ele_ChgComments."',"
					."			Air_Ele_ChgPubWorkflow			= '".$Air_Ele_ChgPubWorkflow."',"
					."			Air_Ele_ChgPendingStatus		= '".$Air_Ele_ChgPendingStatus."',"
					."			Air_Ele_EffDtStart				= '".$Air_Ele_EffDtStart."',"
					."			Air_Ele_EffDtEnd					= '".$Air_Ele_EffDtEnd."',"
					."			Air_Ele_CntElements				= '".$Air_Ele_CntElements."',"
					."			Air_Ele_CntAssociations			= '".$Air_Ele_CntAssociations."',"
					."			Air_Ele_CntProperties			= '".$Air_Ele_CntProperties."',"
					."			Air_Ele_CntRelationships		= '".$Air_Ele_CntRelationships."',"
					."			Air_Ele_RefbyElements			= '".$Air_Ele_RefbyElements."',"
					."			Air_Ele_RefByAssociations		= '".$Air_Ele_RefByAssociations."',"
					."			Air_Ele_RefByProperties			= '".$Air_Ele_RefByProperties."',"
					."			Air_Ele_RefByRelationships		= '".$Air_Ele_RefByRelationships."',"
					."			Air_Ele_EleType					= '".$Air_Ele_EleType."',"
					."			Air_Ele_EleName					= '".$Air_Ele_EleName."',"
					."			Air_Ele_EleContentSize			= '".$Air_Ele_EleContentSize."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
	}

	/*********************************************************
	 * replace_AirEleIndexItem()
	 *******/
	function replace_AirEleIndexItem($colArray)
		{
		$retCode 							= true;
		$Air_Ele_Id							= $colArray['Air_Ele_Id'];
		$Air_Ele_CurrRowStatus			= $colArray['Air_Ele_CurrRowStatus'];
		$Air_Ele_CurrKeyDiscriminator = $colArray['Air_Ele_CurrKeyDiscriminator'];
		$Air_Ele_HiKeySerial				= $colArray['Air_Ele_HiKeySerial'];
		$Air_Ele_CreateDt					= $colArray['Air_Ele_CreateDt'];
		$Air_Ele_CreateEntity			= $colArray['Air_Ele_CreateEntity'];
		$Air_Ele_ChgDt						= $colArray['Air_Ele_ChgDt'];
		$Air_Ele_ChgType					= $colArray['Air_Ele_ChgType'];
		$Air_Ele_ChgEntity				= $colArray['Air_Ele_ChgEntity'];
		$Air_Ele_ChgComments				= $GLOBALS['AF_INSTANCE']->prepTextForSql($colArray['Air_Ele_ChgComments']);
		$Air_Ele_ChgPubWorkflow			= $colArray['Air_Ele_ChgPubWorkflow'];
		$Air_Ele_ChgPendingStatus		= $colArray['Air_Ele_ChgPendingStatus'];
		$Air_Ele_EffDtStart				= $colArray['Air_Ele_EffDtStart'];
		$Air_Ele_EffDtEnd					= $colArray['Air_Ele_EffDtEnd'];
		$Air_Ele_CntElements				= $colArray['Air_Ele_CntElements'];
		$Air_Ele_CntAssociations		= $colArray['Air_Ele_CntAssociations'];
		$Air_Ele_CntProperties			= $colArray['Air_Ele_CntProperties'];
		$Air_Ele_CntRelationships		= $colArray['Air_Ele_CntRelationships'];
		$Air_Ele_RefbyElements			= $colArray['Air_Ele_RefbyElements'];
		$Air_Ele_RefByAssociations		= $colArray['Air_Ele_RefByAssociations'];
		$Air_Ele_RefByProperties		= $colArray['Air_Ele_RefByProperties'];
		$Air_Ele_RefByRelationships	= $colArray['Air_Ele_RefByRelationships'];
		$Air_Ele_EleType					= $colArray['Air_Ele_EleType'];
		$Air_Ele_EleName					= $GLOBALS['AF_INSTANCE']->prepTextForSql($colArray['Air_Ele_EleName']);
		$Air_Ele_EleContentSize			= $colArray['Air_Ele_EleContentSize'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "update air_eleindex"
					."	set	Air_Ele_CurrRowStatus			= '".$Air_Ele_CurrRowStatus."',"
					."			Air_Ele_CurrKeyDiscriminator 	= '".$Air_Ele_CurrKeyDiscriminator."',"
					."			Air_Ele_HiKeySerial				= '".$Air_Ele_HiKeySerial."',"
					."			Air_Ele_CreateDt					= '".$Air_Ele_CreateDt."',"
					."			Air_Ele_CreateEntity				= '".$Air_Ele_CreateEntity."',"
					."			Air_Ele_ChgDt						= '".$Air_Ele_ChgDt."',"
					."			Air_Ele_ChgType					= '".$Air_Ele_ChgType."',"
					."			Air_Ele_ChgEntity					= '".$Air_Ele_ChgEntity."',"
					."			Air_Ele_ChgComments				= '".$Air_Ele_ChgComments."',"
					."			Air_Ele_ChgPubWorkflow			= '".$Air_Ele_ChgPubWorkflow."',"
					."			Air_Ele_ChgPendingStatus		= '".$Air_Ele_ChgPendingStatus."',"
					."			Air_Ele_EffDtStart				= '".$Air_Ele_EffDtStart."',"
					."			Air_Ele_EffDtEnd					= '".$Air_Ele_EffDtEnd."',"
					."			Air_Ele_CntElements				= '".$Air_Ele_CntElements."',"
					."			Air_Ele_CntAssociations			= '".$Air_Ele_CntAssociations."',"
					."			Air_Ele_CntProperties			= '".$Air_Ele_CntProperties."',"
					."			Air_Ele_CntRelationships		= '".$Air_Ele_CntRelationships."',"
					."			Air_Ele_RefbyElements			= '".$Air_Ele_RefbyElements."',"
					."			Air_Ele_RefByAssociations		= '".$Air_Ele_RefByAssociations."',"
					."			Air_Ele_RefByProperties			= '".$Air_Ele_RefByProperties."',"
					."			Air_Ele_RefByRelationships		= '".$Air_Ele_RefByRelationships."',"
					."			Air_Ele_EleType					= '".$Air_Ele_EleType."',"
					."			Air_Ele_EleName					= '".$Air_Ele_EleName."',"
					."			Air_Ele_EleContentSize			= '".$Air_Ele_EleContentSize."'"
					."	where	Air_Ele_Id							= '".$Air_Ele_Id."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
	}

	/*********************************************************
	 * purge_AirEleIndexItem()
	 *******/
	function purge_AirEleIndexItem($Air_Ele_Id)
		{
		$retCode = true;
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "delete from air_eleindex"
					."	where	Air_Ele_Id							= '".$Air_Ele_Id."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
	}

	/*********************************************************
	 * purge_AirEleIndexSet()
	 *******/
	function purge_AirEleIndexSet($Air_Ele_Array)
		{
		if (count($Air_Ele_Array > 0))
			{
			$retCode = true;
			$first	= true;
			if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
			 	{
				$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
				}
			$sqlStmt = "delete from air_eleindex";

			foreach ($Air_Ele_Array as $EleIdent)
				{
				if ($first)
					{
					$sqlStmt .= "	where	Air_Ele_Id = '".$EleIdent."'";
					$first = false;
					}
				else
					{
					$sqlStmt .= " or Air_Ele_Id = '".$EleIdent."'";
					}
				}

			$result = $this->query2($sqlStmt);
			$retCode = $this->successful();
			}
		else
			{
			$retCode = false;
			}

		return($retCode);
		}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// AIR_EleIndex query functions:
	// get_AllIndexItems()			select all index entries
	// get_AirEleIndex()				select a single item by primary key
	// get_RecentAddEleIndex()		select a set created after a specified date
	// get_CreateRangeEleIndex()	select a set created between two dates
	// get_RecentChgEleIndex()		select a set modified after a selected date
	// get_ChangeRangeEleIndex()	select a set modified between two dates
	// get_EleStateIndex()			select a set with a particular row status
	// get_AuthorEleIndex()			select a set authored by a particular entitiy
	// get_ModifierEleIndex()		select a set authored or modified by a particular entity
	// get_PubFlowEleIndex()		select a set using a particular publication workflow
	// get_ChgPendingEleIndex()	select a set where changes are pending
	// get_OrphanEleIndex()			select a set of unconnected elements
	// get_RootNodeEleIndex()		select a set that references others, but is unreferenced
	// get_LeafNodeEleIndex()		select a set that is referenced, but has no details
	// get_BranchNodeEleIndex()	select a set that has referenced on both sides
	// get_EleTypeIndex()			select a set that is of a particular type category
	// get_EleNameIndex()			select a set with element names that follow an initial pattern
	// get_EleNameRegexpIndex()	select a set with element names that follow a regular expression pattern

	/*********************************************************
	 * get_AirEleIndex()
	 *
	 * Obtain the AIR_EleIndex data for a particular element.
	 * This is the direct read of an item by primary key.
	 *******/
	function get_AirEleIndex($eleId)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex
						where Air_Ele_Id = '".$eleId."'";
		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * getCount_AirEleIndex()
	 *
	 * Obtain the count of the number of items in AIR_EleIndex.
	 *******/
	function getCount_AirEleIndex()
		{
		$sqlStmt 		= "select count(*) as TotalRows from air_eleindex";
		$dbCountResult = $this->query1($sqlStmt);
		$dbCountArray 	= $dbCountResult[0];
		$ret				= $dbCountArray['TotalRows'];
		return($ret);
		}

	/*********************************************************
	 * getCountByAttr_AirEleIndex()
	 *
	 * Obtain the count of the number of items of each element
	* type that exist in AIR_EleIndex. The result set is an array
	* that identifies each element type represented and the count
	* of elements of that type.
	 *******/
	function getCountByAttr_AirEleIndex($attribute)
		{
		$sqlStmt 		= "select ".$attribute.","
					 		." count(*) as TotalRows"
					 		." from air_eleindex"
							." GROUP BY ".$attribute;
		$dbCountResult = $this->query1($sqlStmt);
//		$dbCountArray 	= $dbCountResult[0];
//		$ret				= $dbCountArray['TotalRows'];
		return($dbCountResult);
		}

	/*********************************************************
	 * getTypeCount_AirEleIndex()
	 *
	 * Obtain the count of the number of items of the
	 * specified type that exist in AIR_EleIndex.
	 *******/
	function getTypeCount_AirEleIndex($eleType)
		{
		$sqlStmt 		= "select count(*) as TotalRows from air_eleindex"
							." where Air_Ele_EleType = '".$eleType."'";
		$dbCountResult = $this->query1($sqlStmt);
		$dbCountArray 	= $dbCountResult[0];
		$ret				= $dbCountArray['TotalRows'];
		return($ret);
		}

	/*********************************************************
	 * get_AllIndexItems()
	 *
	 * Select all index entries
	 *******/
	function get_AllIndexItems($count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_RecentAddEleIndex()
	 *
	 * Select a set created after a specified date
	 *******/
	function get_RecentAddEleIndex($baseDate, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_CreateDt >= '".$baseDate."'"
					."	order by Air_Ele_CreateDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_CreateRangeEleIndex()
	 *
	 * Select a set created between two dates
	 *******/
	function get_CreateRangeEleIndex($fromDate, $toDate, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_CreateDt >= '".$fromDate."'"
					."   and Air_Ele_CreateDt <= '".$toDate."'"
					."	order by Air_Ele_CreateDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_RecentChgEleIndex()
	 *
	 * Select a set modified after a selected date
	 *******/
	function get_RecentChgEleIndex($baseDate, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_ChgDt >= '".$baseDate."'"
					."	order by Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_ChangeRangeEleIndex()	select a set modified between two dates
	 *******/
	function get_ChangeRangeEleIndex($fromDate, $toDate, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_ChgDt >= '".$fromDate."'"
					."   and Air_Ele_ChgDt <= '".$toDate."'"
					."	order by Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_EleStateIndex()
	 *
	 * Select a set with a particular row status
	 *******/
	function get_EleStateIndex($state, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_CurrRowStatus = '".$state."'"
					."	order by Air_Ele_CurrKeyDiscriminator desc, Air_Ele_ChgDt"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_AuthorEleIndex()
	 *
	 * Select a set authored by a particular entitiy
	 *******/
	function get_AuthorEleIndex($authorId, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_CreateEntity = '".$authorId."'"
					."	order by Air_Ele_EleType, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_ModifierEleIndex()
	 *
	 * Select a set authored or modified by a particular entity
	 *
	 * To be historically accurate, this quesry should really be
	 * based on a relationship tied to the author's EleId. Which
	 * would also be operationally more efficient.
	 * Which is probably true for a number of items. The other
	 * side of the equation being the need to maintain the
	 * links in the database so they exist and can be trusted
	 * when the time comes to use them. The good side being that
	 * those links can be 'intelligent' and 'groomed' and not
	 * just blindly applied as is being done here.
	 *******/
	function get_ModifierEleIndex($authorId, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_CreateEntity = '".$authorId."'"
					."    or Air_Ele_ChgEntity = '".$authorId."'"
					."	order by Air_Ele_EleType, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}


	/*********************************************************
	 * get_PubFlowEleIndex()
	 *
	 * Select a set using a particular publication workflow
	 *******/
	function get_PubFlowEleIndex($pubId, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_ChgPubWorkflow = '".$pubId."'"
					."	order by Air_Ele_EleType, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_ChgPendingEleIndex()
	 *
	 * Select a set where changes are pending
	 *******/
	function get_ChgPendingEleIndex($status, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_ChgPendingStatus = '".$status."'"
					."	order by Air_Ele_EleType, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_OrphanEleIndex()
	 *
	 * Select a set of unconnected elements
	 *******/
	function get_OrphanEleIndex($count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_CntElements = 0"
					."   and Air_Ele_CntAssociations = 0"
					."   and Air_Ele_CntProperties = 0"
					."   and Air_Ele_CntRelationships = 0"
					."   and Air_Ele_RefbyElements = 0"
					."   and Air_Ele_RefbyAssociations = 0"
					."   and Air_Ele_RefbyProperties = 0"
					."   and Air_Ele_RefbyRelationships = 0"
					."	order by Air_Ele_EleType, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_RootNodeEleIndex()
	 *
	 * Select a set that references others, but is unreferenced
	 *******/
	function get_RootNodeEleIndex($count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_RefbyElements = 0"
					."   and Air_Ele_RefbyAssociations = 0"
					."   and Air_Ele_RefbyProperties = 0"
					."   and Air_Ele_RefbyRelationships = 0"
					."	order by Air_Ele_EleType, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_LeafNodeEleIndex()
	 *
	 * Select a set that is referenced, but has no details
	 *******/
	function get_LeafNodeEleIndex($count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_CntElements = 0"
					."   and Air_Ele_CntAssociations = 0"
					."   and Air_Ele_CntProperties = 0"
					."   and Air_Ele_CntRelationships = 0"
					."	order by Air_Ele_EleType, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_BranchNodeEleIndex()
	 *
	 * Select a set that has referenced on both sides
	 *******/
	function get_BranchNodeEleIndex($status, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where (Air_Ele_CntElements != 0 		and Air_Ele_RefbyElements != 0)"
					."    or (Air_Ele_CntAssociations != 0 and Air_Ele_RefbyAssociations != 0"
					."    or (Air_Ele_CntProperties != 0	and Air_Ele_RefbyProperties != 0"
					."    or (Air_Ele_CntRelationships = 0	and Air_Ele_RefbyRelationships != 0"
					."	order by Air_Ele_EleType, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_EleTypeIndex()
	 *
	 * Select a set that is of a particular type category
	 *******/
	function get_EleTypeIndex($eleType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_EleType = '".$eleType."'"
					."	order by Air_Ele_EleName, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_EleNameIndex()
	 *
	 * Select a set with element names that follow an initial pattern
	 *******/
	function get_EleNameIndex($eleName, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_EleName = '".$eleName."' %"
					."	order by Air_Ele_EleType, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_EleNameRegexpIndex()
	 *
	 * Select a set with element names that follow a regular expression pattern
	 *******/
	function get_EleNameRegexpIndex($nameExp, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleindex"
					." where Air_Ele_EleName regexp '".$nameExp."'"
					."	order by Air_Ele_EleType, Air_Ele_ChgDt desc"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	//
	//				R E L A T I O N S H I P   R U L E S
	//

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// AIR_RelRules query functions:

	// insert_AirRelRulesItem			insert a new relationship rule
	// replace_AirRelRulesItem			replace contents of a relationship rule item
	// purge_AirRelRulesItem			delete a single relationship rule set of items
	// get_AllRelRules()					select all relationship rules
	// get_AirRelRule()					select a set of relationship rule records for a single rule
	// get_RelRulesMatchingKey()		select a set of relationship rules match a subject/predicate key
	// get_RelRulesForSubject()		select a set of relationship rules for a subject UUID
	// get_RelRulesUsingPredicate()	select a set of relationship rules for a predicate UUID
	// get_RelRulesUsingElement()		select a set of relationship rules where a UUID appears as subject, object or indirect object

	/*********************************************************
	 * getCount_AirRelRules()
	 *
	 * Obtain the count of the number of items in AirRelRules.
	 *******/
	function getCount_AirRelRules()
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt 		= "select count(*) as TotalRows from air_relrules";
		$dbCountResult = $this->query1($sqlStmt);
		$dbCountArray 	= $dbCountResult[0];
		$ret				= $dbCountArray['TotalRows'];
		return($ret);
		}

	/*********************************************************
	 * insert_AirRelRulesItem()
	 *******/
	function insert_AirRelRulesItem($colArray)
		{
		$retCode 					= true;
		$Air_Ele_Id					= $colArray['Air_Ele_Id'];
		$Air_RelRule_Subject		= $colArray['Air_RelRule_Subject'];
		$Air_RelRule_Predicate	= $colArray['Air_RelRule_Predicate'];
		$Air_RelRule_PredOrd		= $colArray['Air_RelRule_PredOrd'];
		$Air_RelRule_PredCard	= $colArray['Air_RelRule_PredCard'];
		$Air_RelRule_PredMax		= $colArray['Air_RelRule_PredMax'];
		$Air_RelRule_Object		= $colArray['Air_RelRule_Object'];
		$Air_RelRule_IObject		= $colArray['Air_RelRule_IObject'];
		$Air_RelRule_Diag			= $colArray['Air_RelRule_Diag'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "insert into air_relrules"
					."	set	Air_Ele_Id					= '".$Air_Ele_Id					."',"
					."			Air_RelRule_Subject		= '".$Air_RelRule_Subject		."',"
					."			Air_RelRule_Predicate	= '".$Air_RelRule_Predicate	."',"
					."			Air_RelRule_PredOrd		= '".$Air_RelRule_PredOrd		."',"
					."			Air_RelRule_PredCard		= '".$Air_RelRule_PredCard		."',"
					."			Air_RelRule_PredMax		= '".$Air_RelRule_PredMax		."',"
					."			Air_RelRule_Object		= '".$Air_RelRule_Object		."',"
					."			Air_RelRule_IObject		= '".$Air_RelRule_IObject		."',"
					."			Air_RelRule_Diag			= '".$Air_RelRule_Diag			."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * replace_AirRelRulesItem()
	 *******/
	function replace_AirRelRulesItem($colArray)
		{
		$retCode 					= true;
		$Air_Ele_Id					= $colArray['Air_Ele_Id'];
		$Air_RelRule_Subject		= $colArray['Air_RelRule_Subject'];
		$Air_RelRule_Predicate	= $colArray['Air_RelRule_Predicate'];
		$Air_RelRule_PredOrd		= $colArray['Air_RelRule_PredOrd'];
		$Air_RelRule_PredCard	= $colArray['Air_RelRule_PredCard'];
		$Air_RelRule_PredMax		= $colArray['Air_RelRule_PredMax'];
		$Air_RelRule_Object		= $colArray['Air_RelRule_Object'];
		$Air_RelRule_IObject		= $colArray['Air_RelRule_IObject'];
		$Air_RelRule_Diag			= $colArray['Air_RelRule_Diag'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "update air_relrules"
					."	set	Air_RelRule_Subject		= '".$Air_RelRule_Subject		."',"
					."			Air_RelRule_Predicate	= '".$Air_RelRule_Predicate	."',"
					."			Air_RelRule_PredOrd		= '".$Air_RelRule_PredOrd		."',"
					."			Air_RelRule_PredCard		= '".$Air_RelRule_PredCard		."',"
					."			Air_RelRule_PredMax		= '".$Air_RelRule_PredMax		."',"
					."			Air_RelRule_Object		= '".$Air_RelRule_Object		."',"
					."			Air_RelRule_IObject		= '".$Air_RelRule_IObject		."',"
					."			Air_RelRule_Diag			= '".$Air_RelRule_Diag			."'"
					."	where	Air_Ele_Id					= '".$Air_Ele_Id					."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirRelRulesItem()
	 *******/
	function purge_AirRelRulesItem($Air_Ele_Id)
		{
		$retCode = true;
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "delete from air_relrules"
					."	where	Air_Ele_Id						= '".$Air_Ele_Id."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirRelRulesSet()
	 *******/
	function purge_AirRelRulesSet($Air_Ele_Array)
		{
		if (count($Air_Ele_Array > 0))
			{
			$retCode = true;
			$first	= true;
			if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
			 	{
				$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
				}
			$sqlStmt = "delete from air_relrules";

			foreach ($Air_Ele_Array as $EleIdent)
				{
				if ($first)
					{
					$sqlStmt .= "	where	Air_Ele_Id = '".$EleIdent."'";
					$first = false;
					}
				else
					{
					$sqlStmt .= " or Air_Ele_Id = '".$EleIdent."'";
					}
				}

			$result = $this->query2($sqlStmt);
			$retCode = $this->successful();
			}
		else
			{
			$retCode = false;
			}

		return($retCode);
		}

	/*********************************************************
	 * get_AllRelRules()
	 *
	 * Select all entries in the table
	 *******/
	function get_AllRelRules($count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_relrules"
					."	order by Air_Ele_Id"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_AirRelRule()
	 *
	 * Select a single item by primary key
	 *******/
	function get_AirRelRule($eleId)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_relrules
						where Air_Ele_Id = '".$eleId."'";
		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_RelRulesMatchingKey()
	 *
	 * Select the rule set for a given subject element type
	 * and predicate type. There should only be a single rule
	 * at any point in time with this combination.
	 *******/
	function get_RelRulesMatchingKey($subjectEle, $predicateEle, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_relrules"
					." where (Air_RelRule_Subject = '".$subjectEle."'"
					."    or Air_RelRule_Subject = '".AIR_All_Identifier."')"
					."   and Air_RelRule_Predicate = '".$predicateEle."'"
					."	order by Air_Ele_Id"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_RelRulesForSubject()
	 *
	 * Select the rule set for a given subject element type.
	 * This includes all rules specifically designated for the
	 * subject type UUID, and all rules designated for all
	 * elements.
	 *******/
	function get_RelRulesForSubject($subjectEle, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_relrules"
					." where Air_RelRule_Subject = '".$subjectEle."'"
					."    or Air_RelRule_Subject = '".AIR_All_Identifier."'"
					."	order by Air_RelRule_Predicate, Air_RelRule_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_RelRulesUsingPredicate()
	 *
	 * Select a set of 'RelRule' entries that specify a given
	 * relationship type.
	 *******/
	function get_RelRulesUsingPredicate($searchEle, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_relrules"
					." where Air_RelRule_Predicate = '".$searchEle."'"
//					."	order by Air_RelRule_Subject"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_RelRulesUsingElement()
	 *
	 * Select a set of 'RelRule' entries that specify a given
	 * element type as subject, object or indirect object.
	 *******/
	function get_RelRulesUsingElement($subjectEle, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_relrules"
					." where Air_RelRule_Subject 	= '".$subjectEle."'"
					."    or Air_RelRule_Object 	= '".$subjectEle."'"
					."    or Air_RelRule_IObject 	= '".$subjectEle."'"
//					."	order by Air_RelRule_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	//
	//						P R O P E R T I E S
	//

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// AIR_EleProperties query functions:
	// get_AllProperties()		select all properties
	// get_EleProperties()		select a set of all propertiess for an owner element
	// get_PropOfElements()		select a set of all owning elements for a property item
	// get_PropTypeRelations()	select a set of all relationships using a 'PropType' element
	// get_PropTypeItems()		select a set of 'PropType' relationships for an owning element
	// get_PropTypeOwners()		select a set of 'PropType' relationships for an owned (support) element

	/*********************************************************
	 * insert_AirElePropertiesItem()
	 *******/
	function insert_AirElePropertiesItem($colArray)
		{
		$retCode 				= true;
		$Air_Ele_Id				= $colArray['Air_Ele_Id'];
		$Air_Prop_Subject		= $colArray['Air_Prop_Subject'];
		$Air_Prop_Predicate	= $colArray['Air_Prop_Predicate'];
		$Air_Prop_Object		= $colArray['Air_Prop_Object'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "insert into air_eleproperties"
					."	set	Air_Ele_Id				= '".$Air_Ele_Id."',"
					."			Air_Prop_Subject		= '".$Air_Prop_Subject."',"
					."			Air_Prop_Predicate	= '".$Air_Prop_Predicate."',"
					."			Air_Prop_Object	= '".$Air_Prop_Object."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * replace_AirElePropertiesItem()
	 *******/
	function replace_AirElePropertiesItem($colArray)
		{
		$retCode 				= true;
		$Air_Ele_Id				= $colArray['Air_Ele_Id'];
		$Air_Prop_Subject		= $colArray['Air_Prop_Subject'];
		$Air_Prop_Predicate	= $colArray['Air_Prop_Predicate'];
		$Air_Prop_Object		= $colArray['Air_Prop_Object'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "update air_eleproperties"
					."	set	Air_Prop_Subject			= '".$Air_Prop_Subject."',"
					."			Air_Prop_Predicate		= '".$Air_Prop_Predicate."',"
					."			Air_Prop_Object		= '".$Air_Prop_Object."'"
					."	where	Air_Ele_Id					= '".$Air_Ele_Id."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirElePropertiesItem()
	 *
	 * Note. This is a complete purge of everything in the table
	 * under the given ID value.
	 *******/
	function purge_AirElePropertiesItem($Air_Ele_Id)
		{
		$retCode = true;
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "delete from air_eleproperties"
					."	where	Air_Ele_Id						= '".$Air_Ele_Id."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirPropertiesSet()
	 *******/
	function purge_AirPropertiesSet($Air_Ele_Array)
		{
		if (count($Air_Ele_Array > 0))
			{
			$retCode = true;
			$first	= true;
			if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
			 	{
				$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
				}
			$sqlStmt = "delete from air_eleproperties";

			foreach ($Air_Ele_Array as $EleIdent)
				{
				if ($first)
					{
					$sqlStmt .= "	where	Air_Ele_Id = '".$EleIdent."'";
					$first = false;
					}
				else
					{
					$sqlStmt .= " or Air_Ele_Id = '".$EleIdent."'";
					}
				}

			$result = $this->query2($sqlStmt);
			$retCode = $this->successful();
			}
		else
			{
			$retCode = false;
			}

		return($retCode);
		}

	/*********************************************************
	 * getCount_AirProperties()
	 *
	 * Obtain the count of the number of items in AIR_EleProperties.
	 *******/
	function getCount_AirProperties()
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt 		= "select count(*) as TotalRows from air_eleproperties";
		$dbCountResult = $this->query1($sqlStmt);
		$dbCountArray 	= $dbCountResult[0];
		$ret				= $dbCountArray['TotalRows'];
		return($ret);
		}

	/*********************************************************
	 * get_AllProperties()
	 *
	 * Select all properties
	 *******/
	function get_AllProperties($count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleproperties"
					."	order by Air_Ele_Id"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_AirProperty()
	 *
	 * Select a single item by primary key
	 *******/
	function get_AirProperty($eleId)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleproperties
						where Air_Ele_Id = '".$eleId."'";
		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_EleProperties()
	 *
	 * Select a set of all propertiess for an owner element
	 *******/
	function get_EleProperties($ownerEle, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleproperties"
					." where Air_Prop_Subject = '".$ownerEle."'"
					."	order by Air_Prop_Predicate, Air_Prop_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_PropOfElements()
	 *
	 * Select a set of all owning elements for a property item
	 *******/
	function get_PropOfElements($itemEle, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleproperties"
					." where Air_Prop_Object = '".$itemEle."'"
					."	order by Air_Prop_Subject, Air_Prop_Predicate"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_PropTypeRelations()
	 *
	 * Select a set of all relationships using a 'PropType' element
	 *******/
	function get_PropTypeRelations($propType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleproperties"
					." where Air_Prop_Predicate = '".$propType."'"
					."	order by Air_Prop_Subject, Air_Prop_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_PropTypeItems()
	 *
	 * Select a set of 'PropType' relationships for an owning element
	 *******/
	function get_PropTypeItems($ownerEle, $propType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleproperties"
					." where Air_Prop_Subject = '".$ownerEle."'"
					."   and Air_Prop_Predicate = '".$propType."'"
					."	order by Air_Prop_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_PropTypeOwners()
	 *
	 * Select a set of 'PropType' relationships for an owned (support) element
	 *******/
	function get_PropTypeOwners($itemEle, $propType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleproperties"
					." where Air_Prop_Object = '".$itemEle."'"
					."   and Air_Prop_Predicate = '".$propType."'"
					."	order by Air_Prop_Subject"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	//
	// 					A S S O C I A T I O N S
	//

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// AIR_EleAssociations query functions:
	// get_AllAssociations()		select all associations
	// get_AirAssociation()			select a single item by primary key
	// get_EleAssociations()		select a set of all associations for a 'from' element
	// get_AssocByElements()		select a set of all 'from' elements for an associated item
	// get_AssocTypeRelations()	select a set of all relationships using a 'AssocType' element
	// get_AssocTypeObjects()		select a set of 'AssocType' relationships for a 'from' element
	// get_AssocTypeSubjects()		select a set of 'AssocType' relationships for a 'to' element
	// get_XmitAssociations()		select a set of all associations for an 'xmit' item
	// get_AssocTypeXmit()			select a set of 'AssocType' relationships for an 'xmit' element

	/*********************************************************
	 * insert_AirAssociationsItem()
	 *******/
	function insert_AirAssociationsItem($colArray)
		{
		$retCode 					= true;
		$Air_Ele_Id					= $colArray['Air_Ele_Id'];
		$Air_Assoc_Subject		= $colArray['Air_Assoc_Subject'];
		$Air_Assoc_Predicate		= $colArray['Air_Assoc_Predicate'];
		$Air_Assoc_Object			= $colArray['Air_Assoc_Object'];
		$Air_Assoc_IObject		= $colArray['Air_Assoc_IObject'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "insert into air_eleassociations"
					."	set	Air_Ele_Id					= '".$Air_Ele_Id."',"
					."			Air_Assoc_Subject			= '".$Air_Assoc_Subject."',"
					."			Air_Assoc_Predicate	= '".$Air_Assoc_Predicate."',"
					."			Air_Assoc_Object			= '".$Air_Assoc_Object."',"
					."			Air_Assoc_IObject			= '".$Air_Assoc_IObject."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * replace_AirAssociationsItem()
	 *******/
	function replace_AirAssociationsItem($colArray)
		{
		$retCode 					= true;
		$Air_Ele_Id					= $colArray['Air_Ele_Id'];
		$Air_Assoc_Subject		= $colArray['Air_Assoc_Subject'];
		$Air_Assoc_Predicate		= $colArray['Air_Assoc_Predicate'];
		$Air_Assoc_Object			= $colArray['Air_Assoc_Object'];
		$Air_Assoc_IObject		= $colArray['Air_Assoc_IObject'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "update air_eleassociations"
					."	set	Air_Assoc_Subject			= '".$Air_Assoc_Subject."',"
					."			Air_Assoc_Predicate	= '".$Air_Assoc_Predicate."',"
					."			Air_Assoc_Object			= '".$Air_Assoc_Object."',"
					."			Air_Assoc_IObject			= '".$Air_Assoc_IObject."'"
					."	where	Air_Ele_Id					= '".$Air_Ele_Id."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirAssociationsItem()
	 *
	 * Note. This is a complete purge of everything in the table
	 * under the given ID value.
	 *******/
	function purge_AirAssociationsItem($Air_Ele_Id)
		{
		$retCode = true;
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "delete from air_eleassociations"
					."	where	Air_Ele_Id						= '".$Air_Ele_Id."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirAssociationsSet()
	 *******/
	function purge_AirAssociationsSet($Air_Ele_Array)
		{
		if (count($Air_Ele_Array > 0))
			{
			$retCode = true;
			$first	= true;
			if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
			 	{
				$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
				}
			$sqlStmt = "delete from air_eleassociations";

			foreach ($Air_Ele_Array as $EleIdent)
				{
				if ($first)
					{
					$sqlStmt .= "	where	Air_Ele_Id = '".$EleIdent."'";
					$first = false;
					}
				else
					{
					$sqlStmt .= " or Air_Ele_Id = '".$EleIdent."'";
					}
				}

			$result = $this->query2($sqlStmt);
			$retCode = $this->successful();
			}
		else
			{
			$retCode = false;
			}

		return($retCode);
		}

	/*********************************************************
	 * getCount_AirAssociations()
	 *
	 * Obtain the count of the number of items in AIR_EleAssociations.
	 *******/
	function getCount_AirAssociations()
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt 		= "select count(*) as TotalRows from air_eleassociations";
		$dbCountResult = $this->query1($sqlStmt);
		$dbCountArray 	= $dbCountResult[0];
		$ret				= $dbCountArray['TotalRows'];
		return($ret);
		}

	/*********************************************************
	 * get_AllAssociations()
	 *
	 * Select all associations
	 *******/
	function get_AllAssociations($count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleassociations"
					."	order by Air_Ele_Id"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_AirAssociation()
	 *
	 * Select a single item by primary key
	 *******/
	function get_AirAssociation($eleId)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleassociations
						where Air_Ele_Id = '".$eleId."'";
		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_EleAssociations()
	 *
	 * Select a set of all associations for a 'from' element
	 *******/
	function get_EleAssociations($fromEle, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleassociations"
					." where Air_Assoc_Subject = '".$fromEle."'"
					."	order by Air_Assoc_Predicate, Air_Assoc_Object, Air_Assoc_IObject"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_AssocByElements()
	 *
	 * Select a set of all 'from' elements for an associated item
	 *******/
	function get_AssocByElements($toEle, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleassociations"
					." where Air_Assoc_Object = '".$toEle."'"
					."	order by Air_Assoc_Subject, Air_Assoc_Predicate, Air_Assoc_IObject"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_AssocTypeRelations()
	 *
	 * Select a set of all relationships using a 'AssocType' element
	 *******/
	function get_AssocTypeRelations($assocType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleassociations"
					." where Air_Assoc_Predicate = '".$assocType."'"
					."	order by Air_Assoc_Subject, Air_Assoc_Object, Air_Assoc_IObject"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_AssocTypeObjects()
	 *
	 * Select a set of 'AssocType' relationships for a 'from' element
	 *******/
	function get_AssocTypeObjects($fromEle, $assocType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleassociations"
					." where Air_Assoc_Subject = '".$fromEle."'"
					."   and Air_Assoc_Predicate = '".$assocType."'"
					."	order by Air_Assoc_Object, Air_Assoc_IObject"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_AssocTypeSubjects()
	 *
	 * Select a set of 'AssocType' relationships for a 'to' element
	 *******/
	function get_AssocTypeSubjects($toEle, $assocType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleassociations"
					." where Air_Assoc_Object = '".$toEle."'"
					."   and Air_Assoc_Predicate = '".$assocType."'"
					."	order by Air_Assoc_Subject, Air_Assoc_IObject"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_XmitAssociations()
	 *
	 * Select a set of all associations for an 'xmit' item
	 *******/
	function get_XmitAssociations($xmitEle, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleassociations"
					." where Air_Assoc_IObject = '".$xmitEle."'"
					."	order by Air_Assoc_Subject, Air_Assoc_Predicate, Air_Assoc_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_AssocTypeXmit()
	 *
	 * Select a set of 'AssocType' relationships for an 'xmit' element
	 *******/
	function get_AssocTypeXmit($xmitEle, $assocType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_eleassociations"
					." where Air_Assoc_IObject = '".$xmitEle."'"
					."   and Air_Assoc_Predicate = '".$assocType."'"
					."	order by Air_Assoc_Subject, Air_Assoc_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	//
	// 					R E L A T I O N S H I P S
	//

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// AIR_EleRelationships query functions:
	// get_AllRelationships()	select all relationships
	// get_EleRelationship()	select a single relationship by primary key
	// get_EleRelations()		select a set of all relationships for a 'from' element
	// get_RelatedByElements()	select a set of all relationships for a 'to' element
	// get_RelTypeRelations()	select a set of all relationships using a 'RelType' element
	// get_RelTypeObjects()		select a set of 'type' relationships for a 'from' element
	// get_RelTypeSubjects()	select a set of 'type' relationships for a 'to' element
	//
	// Note. "ORDER BY" clauses are only valuable for clustering of relationships
	//       by object or type. There is no significance within a column to
	//       the row sequence since element identifiers are, in effect, random numbers.

	/*********************************************************
	 * insert_AirEleRelationshipsItem()
	 *******/
	function insert_AirEleRelationshipsItem($colArray)
		{
		$retCode 				= true;
		$Air_Rel_Subject		= $colArray['Air_Rel_Subject'];
		$Air_Rel_Predicate	= $colArray['Air_Rel_Predicate'];
		$Air_Rel_Object		= $colArray['Air_Rel_Object'];
		$Air_Rel_RefCount		= $colArray['Air_Rel_RefCount'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "insert into air_elerelationships"
					."	set	Air_Rel_Subject		= '".$Air_Rel_Subject."',"
					."			Air_Rel_Predicate	= '".$Air_Rel_Predicate."',"
					."			Air_Rel_Object			= '".$Air_Rel_Object."',"
					."			Air_Rel_RefCount		= '".$Air_Rel_RefCount."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * replace_AirEleRelationshipItem()
	 *******/
	function replace_AirEleRelationshipItem($colArray)
		{
		$retCode 				= true;
		$Air_Rel_Subject		= $colArray['Air_Rel_Subject'];
		$Air_Rel_Predicate	= $colArray['Air_Rel_Predicate'];
		$Air_Rel_Object		= $colArray['Air_Rel_Object'];
		$Air_Rel_RefCount		= $colArray['Air_Rel_RefCount'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "update air_elerelationships"
					."	set	Air_Rel_RefCount		= '".$Air_Rel_RefCount."'"
					." where Air_Rel_Subject		= '".$Air_Rel_Subject."'"
					." and Air_Rel_Predicate		= '".$Air_Rel_Predicate."'"
					." and Air_Rel_Object				= '".$Air_Rel_Object."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirEleRelationshipItem()
	 *
	 * Note. This is a purge of the table for a particular relationship.
	 *******/
	function purge_AirEleRelationshipItem($colArray)
		{
		$retCode 				= true;
		$Air_Rel_Subject		= $colArray['Air_Rel_Subject'];
		$Air_Rel_Predicate	= $colArray['Air_Rel_Predicate'];
		$Air_Rel_Object		= $colArray['Air_Rel_Object'];

		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "delete from air_elerelationships"
					." where Air_Rel_Subject		= '".$Air_Rel_Subject."'"
					." and Air_Rel_Predicate		= '".$Air_Rel_Predicate."'"
					." and Air_Rel_Object				= '".$Air_Rel_Object."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirEleRelationships()
	 *
	 * Note. This is a complete purge of everything in the table
	 * under the given ID value.
	 *******/
	function purge_AirEleRelationships($Air_Ele_Id)
		{
		$retCode = true;
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "delete from air_elerelationships"
					." where Air_Rel_Subject = '".$Air_Ele_Id."'"
					." or Air_Rel_Predicate = '".$Air_Ele_Id."'"
					." or Air_Rel_Object = '".$Air_Ele_Id."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirRelationshipsSet()
	 *******/
	function purge_AirRelationshipsSet($Air_Ele_Array)
		{
		if (count($Air_Ele_Array > 0))
			{
			$retCode = true;
			foreach ($Air_Ele_Array as $EleIdent)
				{
				$retCode = $this->purge_AirEleRelationships($EleIdent);
				}
			}
		else
			{
			$retCode = false;
			}

		return($retCode);
		}

	/*********************************************************
	 * getCount_AirRelationships()
	 *
	 * Obtain the count of the number of items in AIR_EleRelationships.
	 *******/
	function getCount_AirRelationships()
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt 		= "select count(*) as TotalRows from air_elerelationships";
		$dbCountResult = $this->query1($sqlStmt);
		$dbCountArray 	= $dbCountResult[0];
		$ret				= $dbCountArray['TotalRows'];
		return($ret);
		}

	/*********************************************************
	 * get_AllRelationships)
	 *
	 * Select all relationships
	 *******/
	function get_AllRelationships($count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_elerelationships"
					."	order by Air_Rel_Subject, Air_Rel_Predicate, Air_Rel_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_EleRelationship()
	 *
	 * Select a single relationship by primary key.
	 *******/
	function get_EleRelationship($fromRel, $relType, $toRel)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_elerelationships"
					." where Air_Rel_Subject = '".$fromRel."'"
					." and Air_Rel_Predicate = '".$relType."'"
					." and Air_Rel_Object = '".$toRel."'";

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_EleRelations()
	 *
	 * Select a set of all relationships for a 'from' element
	 *******/
	function get_EleRelations($fromRel, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_elerelationships"
					." where Air_Rel_Subject = '".$fromRel."'"
					."	order by Air_Rel_Predicate, Air_Rel_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_RelatedByElements()
	 *
	 * Select a set of all relationships for a 'to' element
	 *******/
	function get_RelatedByElements($toRel, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_elerelationships"
					." where Air_Rel_Object = '".$toRel."'"
					."	order by Air_Rel_Subject, Air_Rel_Predicate"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*******************************************************
	 * get_RelTypeRelations()
	 *
	 * Select a set of all relationships using a 'RelType' element
	 *******/
	function get_RelTypeRelations($relType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_elerelationships"
					." where Air_Rel_Predicate = '".$relType."'"
					."	order by Air_Rel_Subject, Air_Rel_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_RelTypeObjects()
	 *
	 * Select a set of 'type' relationships for a 'from' element
	 *******/
	function get_RelTypeObjects($fromRel, $relType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_elerelationships"
					." where Air_Rel_Subject = '".$fromRel."'"
					."   and Air_Rel_Predicate = '".$relType."'"
					."	order by Air_Rel_Subject, Air_Rel_Predicate, Air_Rel_Object"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}

	/*********************************************************
	 * get_RelTypeSubjects()
	 *
	 * Select a set of 'type' relationships for a 'to' element
	 *******/
	function get_RelTypeSubjects($toRel, $relType, $count, $start=0)
		{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "select * from air_elerelationships"
					." where Air_Rel_Object = '".$toRel."'"
					."   and Air_Rel_Predicate = '".$relType."'"
					."	order by Air_Rel_Subject"
					." limit ".$start.",".$count;

		$ret = $this->query1($sqlStmt);
		return($ret);
		}


	/*********************************************************
	 * get_AirTableItem()
	 *
	 * Select the database item by key of Air_Ele_Id
	 *
	 * Returns an array of items (size s/b 1)
	 *    each item in the array is an array of fields
	 *    keyed by field name == database field name
	 *******/
	function & get_AirTableItem($table, $Air_Ele_Id)
	{
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
		}

		$sqlStmt = "select * from ".$table
					."	where	Air_Ele_Id						= '".$Air_Ele_Id."'";
		$dbData = $this->query1($sqlStmt);

		if ((!isset($dbData))
		 || (is_null($dbData))
		 || (!is_array($dbData)))
			{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Invalid DB query return');
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, $GLOBALS['AF_INSTANCE']->whereAmI());
			die ('Invalid DB query return to ' . __FUNCTION__);
			}


		return($dbData);
	}

	/*********************************************************
	 * purge_AirTableItem()
	 *******/
	function purge_AirTableItem($table, $Air_Ele_Id)
		{
		$retCode = true;
		if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
		 	{
			$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		$sqlStmt = "delete from ".$table
					."	where	Air_Ele_Id						= '".$Air_Ele_Id."'";

		$result = $this->query2($sqlStmt);
		$retCode = $this->successful();
		return($retCode);
		}

	/*********************************************************
	 * purge_AirTableItemCollection()
	 *******/
	function purge_AirTableItemCollection($table, $Air_Ele_Array)
		{
		if (count($Air_Ele_Array > 0))
			{
			$retCode = true;
			$first	= true;
			if ($GLOBALS['AF_INSTANCE']->debugDatabaseAccess())
			 	{
				$GLOBALS['AF_INSTANCE']->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
				}
			$sqlStmt = "delete from ".$table;

			foreach ($Air_Ele_Array as $EleIdent)
				{
				if ($first)
					{
					$sqlStmt .= "	where	Air_Ele_Id = '".$EleIdent."'";
					$first = false;
					}
				else
					{
					$sqlStmt .= " or Air_Ele_Id = '".$EleIdent."'";
					}
				}

			$result = $this->query2($sqlStmt);
			$retCode = $this->successful();
			}
		else
			{
			$retCode = false;
			}

		return($retCode);
		}

	} // End of class C_AF_AirDatabase

?>