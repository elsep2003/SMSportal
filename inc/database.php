<?php
/**
* Database connection class
* This class is used to connect to MS SQL and MY SQL databases.
*
* @author Christopher Rouse
* @date Created April 2016 
* @license http://www.php.net/license/3_01.txt PHP License 3.01

Notes:

connection expects

$serverName - Server name
$connectionInfo - Database connection details.
$connectionInfo = array(
        "UID" => "uname",  
        "PWD" => "password", 
        "Database"=>"DB",
        "MultipleActiveResultSets" => false, MSQL only
        'ReturnDatesAsStrings'=>true MSQL only
        
        
*/

class Database
{
    /**
    * Public variables
    *
    * None
    *
    *Private variables
    * @var DATABASE_MYSQL - Constant variable used to give a friendly name in case statements.
    * @var DATABASE_MSSQL - Constant variable used to give a friendly name in case statements.
    * @var $dbtype -  an int value that identifies which DB is used.  1 = MY SQL, 2= MS SQL.
    * @var $con - Database connection string
    */
    
    const DATABASE_MYSQL = 1;
    const DATABASE_MSSQL = 2;
    private $dbtype; 
    public $con;
	public $sqlerr;
    public $insert_id;
	/**
    * This public function creates a connection to a MY SQL database and places connection handle in the $con handle.
    *
    * @param $serverName - a String that holds hostname or IP address of the SQL server to be connected to.
    * @param $connectionString - An Array that hold the connection details for the database. See the note as top of page for the array values.
    *
    * @return - This has no returns
    */
    public function MyConnect($serverName, $connectionString)
    {
        $this->dbtype = self::DATABASE_MYSQL;
         $this->con = mysqli_connect($serverName,$connectionString['UID'],$connectionString['PWD'],$connectionString['Database']);
        if (! $this->con) { die("Debugging errno: " . mysqli_connect_errno(). " ". mysqli_connect_error()  . PHP_EOL); }
    }

    /**
    * This public function creates a connection to a MS SQL database and places connection handle in the $con handle.
    *
    * @param $serverName - a String that holds hostname or IP address of the SQL server to be connected to.
    * @param $connectionString - An Array that hold the connection details for the database. See the note as top of page for the array values.
    *
    * @return - This has no returns
    */
    public function SQLConnect($serverName, $connectionString)
    {
        $this->dbtype = self::DATABASE_MSSQL;
         $this->con = sqlsrv_connect($serverName, $connectionString);
        if (! $this->con) { die(print_r(sqlsrv_errors(), true)); }
        
    }
    
    /**
    * This public function generates DB specific SQL and returns a record set to be used outside the class.
    *
    * @param $sql - a String that holds a pre created SQL Statement with ? highlighting where data should be imported. This method is used instead of direct queries to minimise SQL injection.  
    * @param $params - An Array that hold the values to be inserted into predefined query.  
    *        With MY SQL a data type field should be provided(see mysqli bind_param) e.g Query sql "SELECT id,name, location FROM table WHERE id = ? and name like ?"
    *        param Array MYSQL =array("is",1,"dave%") , where as MS SQL would look like MSSQL =array(1,"dave%")
    *
    * @return - This will return a get_result object with MYSQL or sqlsrv_execute reuslt with MS SQL.
    */
    
    public function Query($sql, array $params)
    {
       
		switch ($this->dbtype)
        {
        case self::DATABASE_MYSQL:
            
            $this->sqlerr=0;
			$this->insert_id = null;
            if (count($params)>=1)
            {
                // Work around for pointers.
				$tmp = array();
				foreach($params as $key => $value) $tmp[$key] = &$params[$key];
				
				$query=mysqli_prepare( $this->con,$sql);
                $method = new ReflectionMethod('mysqli_stmt', 'bind_param'); 
                $method->invokeArgs($query,$params); 
				
            }
            else
            {
                
                $query=mysqli_prepare($this->con,$sql);
                
            }
             
            if ($query)
            {
            $query->execute();
			$this->insert_id = $query->insert_id;
			$this->sqlerr=mysqli_errno($this->con);
			}
            return $query->get_result();
        break;
        
        case self::DATABASE_MSSQL:
            echo "MS SQL";
            
            $query=sqlsrv_prepare($this->con,$sql,$params);
            sqlsrv_execute($query);
            return $query;
        break;
        
        default:
            echo "Oops something when wrong";
        break; 
     }
    }
    /**
    * This public function Cleanly closed the DB connection.
    * @param - None
    * @return - None
    *
    */
    public function disconect()
    {
        switch ($this->dbtype)
            {
            case self::DATABASE_MYSQL:
                mysqli_close($this->con);  
            break;
            case self::DATABASE_MSSQL:
               sqlsrv_close($this->con);
            break;
            
         }
    }
}
?>