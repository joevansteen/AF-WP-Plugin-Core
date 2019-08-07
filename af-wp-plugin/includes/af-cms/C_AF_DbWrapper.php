<?php
/*
 * C_AF_DbWrapper script Copyright (c) 2005, 2008 Architected Futures, LLC
 *
 * V1.3 2005-OCT-17 JVS Begin test of new standalone PHP environment script
 * V1.7 2008-MAR-03 JVS Code conversion to PHPv5.2
 * V1.8 2008-APR-06 JVS C_AF_DbWrapper refactored from af_dbaccess
 * V5   2019-APR-02 JVS Change mysql_ to mysqli_ from PHP5 to PHP7 base for WP
 */

// Insure a correct execution context ...
$myProcClass = 'C_AF_DbWrapper';
$myDynamClass = $myProcClass;	
require_once(AF_CORE_SCRIPTS.'/af_script_header.php');

	/***************************************************************************
	 * C_AF_DbWrapper
	 *
	 * This class defines the SQL database abstraction mechanism
	 * for access to a coordinated set of AIR database tables. The remainder of the
	 * AIR library classes use this class for access to the database.
	 
	 * An array of instances of this class is used to provide consistent indirection
	 * to instances of AIR data in discrete management sets.
	 
	 * External elements use the Air class objects for reference to
	 * the data. <br />
	 * Currently this class provides an interface to the database using an
	 * MySQLi connection.
	 ***************************************************************************/
class C_AF_DbWrapper extends C_AirObjectBase {
 
	var $db_open				= false;
	var $db_link				= false;
	var $db_conn				= false;
	var $db_lastOperErr		= false;
	var $db_lastOperRows		= 0;
	var $db_lastErrorText	= '';
	var $db_lastQuery			= '';

	// --------------------------------------------------------
	// Constructor
	//
	// Initialize the local variable store and creates a local
	// reference to the AIR_anchor object for later use in
	// detail function processing. (Be careful with code here
	// to ensure that we are really talking to the right object.)
	// --------------------------------------------------------
	function __construct( $anchor )
		{
		// Propogate the construction process
		parent::__construct( $anchor );

		if (($this->anchor != NULL) && ($this->anchor->trace()))
		 	{
			$this->anchor->putTraceData(__LINE__, __FILE__, 'Executing ' . __CLASS__ . '::' . __FUNCTION__);
			}
		}

	/***************************************************************************
	 * connect
	 *******/
	function connect($host, $database, $user, $pswd)
	 	{
	 	// Follow connect uses database name as the link ID
		$this->db_link = new mysqli($host, $user, $pswd, $database);
	 	if ((! $this->db_link) || ($this->db_link->connect_error)) {
	 		trigger_error('Cannot connect to database: '.$database.' '. mysqli_error($this->db_link), E_USER_ERROR);
	 		die('Cannot connect to database: '.$database.' '. mysqli_error($this->db_link).' '. $conn->connect_error);
	 		}
 		// Redundant, change the connection to "$database" for $database above
 		$this->db_conn = mysqli_select_db($this->db_link, $database);
	 	if (! $this->db_conn)
	 		{
	 		trigger_error('Cannot select database: '.$database.' '. mysqli_error($this->db_link), E_USER_ERROR);
	 		exit;
	 		}

		$this->db_lastOperErr	= false;
		$this->db_lastErrorText	= '';
		$this->db_lastQuery		= '';

	 	$this->db_open				= true;
		}

	/***************************************************************************
	 * terminate
	 *
	 * Termination housekeeping.
	 *******/
	function terminate()
		{
		if ($this->db_open)
			{
		  	$sqlResult = mysqli_close($this->db_link);
	 		$this->db_link		= false;
	 		$this->db_open		= false;
			}
		parent::terminate();
		}

	/***************************************************************************
	 * successful
	 *
	 * Reports the result of the last DB query.
	 *******/
	function successful()
		{
		return(! $this->db_lastOperErr);
		}

	/***************************************************************************
	 * query1()
	 *
	 * For use with select, show, describe or explain statements. mysql returns a
	 * resource on success, or FALSE.
	 *******/
	function query1($sqlStmt)
		{
		$myResult = false;

		$this->db_lastOperErr	= false;
		$this->db_lastErrorText	= '';
		$this->db_lastQuery		= $sqlStmt;
		$this->db_lastOperRows	= 0;

		if ($this->db_open)
			{
		  	$sqlResult = mysqli_query($this->db_link, $sqlStmt);
		 	if (! $sqlResult)
		 		{
				$this->db_lastOperErr	= true;
				$this->db_lastErrorText	= mysqli_error($this->db_link);
		 		trigger_error('Query processing error: '. $this->db_lastErrorText, E_USER_ERROR);
		 		$myResult = array();
		 		}
		 	else
		 		{
		 		$myResult = array();
				$this->db_lastOperRows = mysqli_num_rows($sqlResult);
				if ($this->db_lastOperRows > 0)
					{
			 		while ($row = mysqli_fetch_assoc($sqlResult))
			 			{
			 			$myResult[] = $row;
		 				}
		 			mysqli_free_result($sqlResult);
		 			}
		 		}
			}
		else
			{
			$this->db_lastOperErr	= true;
			$this->db_lastErrorText	= 'DB facility not initialized.';
			}

		return($myResult);
		}

	/***************************************************************************
	 * query2()
	 *
	 * For use with remaining SQL statements: update, delete, drop, etc. mysql
	 * returns TRUE on success or FALSE on error.
	 *******/
	function query2($sqlStmt)
	{
		$sqlResult = false;

		$this->db_lastOperErr	= false;
		$this->db_lastErrorText	= '';
		$this->db_lastQuery		= $sqlStmt;
		$this->db_lastOperRows	= 0;

		if ($this->db_open)
		{
		  	$sqlResult = mysqli_query($this->db_link, $sqlStmt);
		 	if (! $sqlResult)
	 		{
				$this->db_lastOperErr	= true;
				$this->db_lastErrorText	= mysqli_error($this->db_link);
		 		trigger_error('Query processing error: '.$this->db_lastErrorText.' in query:<br/>'.$sqlStmt.'<br/>', E_USER_WARNING);
	 		}
		 	else
		 	{
		 		$this->db_lastOperRows = mysqli_affected_rows($this->db_link);
		 	}
		}
		else
		{
			$this->db_lastOperErr	= true;
			$this->db_lastErrorText	= 'DB facility not initialized.';
		}

		return($sqlResult);
	}

	/***************************************************************************
	 * airQuery()
	 *
	 * Not currently used. This is a standardized query that was used previously
	 * for DB access through the PEAR DB abstraction library.
	 *
	 * Perform a standardized query operation against one of the AIR tables.
	 * Handles any error response codes. If no error, returns results in a
	 * standardized array.
	 *******/
	function airQuery($sqlStmt)
		{
		$result = $this->db->query($sqlStmt);
		if (DB::isError($result))
			{
			$this->sql_error($sqlStmt, $result);
			}
		$ret = Array();
		while ($res = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
			$ret[] = $res;
			}
		return($ret);
		}

	// --------------------------------------------------------
	// sql_error()
	// Reports SQL error from PEAR::db object.
	// --------------------------------------------------------
	function sql_error($sqlStmt, $result)
		{
		global $ADODB_LASTDB;

		$errMsg = $ADODB_LASTDB.' error:   '.$result->getMessage().'in query:<br/>'.$sqlStmt;
		$this->anchor->abort($errMsg);
		}

	} // end of class

?>