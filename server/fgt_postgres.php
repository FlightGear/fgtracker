<?php
class fgt_postgres{
	/*Postgres manager
	Manage connections to postgresSQL
	*/
	var $conn;
	var $connected;
	var $inTransaction;
	
	public function __construct ($appname) 
	{
		global $var;
		$this->connected=false;
		$this->conn=NULL;
		$this->connectmaster($appname);
		$this->inTransaction=FALSE;	
	}
	
	public function connectmaster($appname)
	{
		/*connect to PostgreSQL. This function will never return until a SQL connection has been successfully made
		This function will close all connections with fgms before connecting to Database
		return true if a new SQL connection has been made.
		return false if SQL connection is untouched
		*/
		global $var,$fgt_error_report,$fgt_conn;
		/*No connection is needed if PostgreSQL is connected*/
		if ($this->connected===true)
			return false;
		
		/* if $fgt_conn is not null, close all connection first*/
		if ($fgt_conn !=null)
		{
			$fgt_conn->close_all_connections();
			$fgt_conn=null;
		}
		if ($this->conn!=NULL)
		{
			pg_close ($this->conn);
			$this->conn=NULL;
		}
		/*connect to Server 1*/
		$message="Connecting to postgres server - ".$var['postgre_conn']['desc'];
		$fgt_error_report->fgt_set_error_report("CORE",$message,E_NOTICE);	

		if ($var['postgre_conn']['host']=="")
		$conn1=pg_connect("dbname=".$var['postgre_conn']['db']." user=".$var['postgre_conn']['uname']." password=".$var['postgre_conn']['pass'] ." connect_timeout=5",PGSQL_CONNECT_FORCE_NEW);
		else
		$conn1=pg_connect("host=".$var['postgre_conn']['host']." port=".$var['postgre_conn']['port']." dbname=".$var['postgre_conn']['db']." user=".$var['postgre_conn']['uname']." password=".$var['postgre_conn']['pass'] ." connect_timeout=5",PGSQL_CONNECT_FORCE_NEW);
		if ($conn1 ===FALSE)
		{
			$message="Failed to connect to postgres server - ".$var['postgre_conn']['desc'].". Will retry in 30 seconds";
			$fgt_error_report->fgt_set_error_report("CORE",$message,E_WARNING);	
			$last_failed=time();
			while (1)
			{
				sleep(1);
				if(time()-$last_failed>30)
				{
					$this->connectmaster($appname);
					break;
				}
				if($var['exitflag']===true)
					exit;
			}
			return true;
		}
		
		$message="Connected to postgres server - ".$var['postgre_conn']['desc'];
		$fgt_error_report->fgt_set_error_report("CORE",$message,E_WARNING);	
		
		$res=pg_query($conn1,"SET TIMEZONE TO 'UTC';");
		$res=pg_query($conn1,"SET application_name = '$appname';");
		pg_free_result($res);
		$this->connected=true;
		$this->conn=$conn1;
		if (class_exists("fgt_connection_mgr"))
			$fgt_conn=new fgt_connection_mgr(); /*use in server only*/
		return true;
	}
	
	function check_no_of_FGTracker_instance($max_allowed)
	{
		global $var,$fgt_error_report,$fgt_conn;
		$res=pg_query($this->conn,"SELECT pid, application_name FROM pg_stat_activity where application_name LIKE 'FGTracker V%';");
		if ($res===false)
			return false;
		$nr=pg_num_rows($res);
		pg_free_result($res);
		if($nr>$max_allowed)
			return false;
		else return true;
	}
	
	function check_no_of_instance($appname,$max_allowed)
	{	/*appname without version number. Ruturn true if not exceed. Max allowed should include instance itself (i.e. $max_allowed should be at least 1.)*/
		global $var,$fgt_error_report,$fgt_conn;
		$res=pg_query($this->conn,"SELECT pid, application_name FROM pg_stat_activity where application_name LIKE '$appname%';");
		if ($res===false)
			return false;
		$nr=pg_num_rows($res);
		pg_free_result($res);
		if($nr>$max_allowed)
			return false;
		else return true;
	}
	/*Not necessary to be called as connection is non-presistent
	function __destruct()
	{	
		if($this->connected===false)
			return;
		
		if(pg_close ($this->conn)===true)
		{	
			$message="Postgres server - ".$var['postgre_conn']['desc']." closed";
			$this->fgt_set_error_report("CORE",$message,E_WARNING);
			$this->connected=false;
			return true;
		}else
		{
			$message="Failed to close postgres server - ".$var['postgre_conn']['desc'];
			$this->fgt_set_error_report("CORE",$message,E_ERROR);
			return false;
		}
			
	}*/
}

?>