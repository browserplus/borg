<?php
define("DB_CONN", "/home/borg/dbpasswd.json");

class DB
{
	private $dbh;
	
	function __construct($conn_name) {
		try {
            $json = json_decode(file_get_contents(DB_CONN), true);
            $conn = $json[$conn_name];

            $h = $conn["server"];
            $t = $conn["db"];
            $u = $conn["user"];
            $p = $conn["pass"];

			$this->dbh = new PDO("mysql:host=$h;dbname=$t", $u, $p);
		} catch (PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage();
			exit;
		}
	}

    private function err($s) {
        error_log($s, 3, "/tmp/dberrors.txt");        
    }

    private function _ensure_array($value) {
        return (is_array($value) ? $value : array($value));
    }

    /*
     * Simple execute for any query.
     */
    function execute($sql, $vars) {
		$sth = $this->dbh->prepare($sql);
		if ($sth->execute($vars)) {
		    return true;
		} else {
            $this->err($sth->errorInfo());
            return false;
		}
    }

    /*
    * Return max id from table.
    */
    function max_id($table, $id="id")
    {
        $sth = $this->dbh->prepare($sql="SELECT MAX($id) FROM $table");
        if ($sth == false) {
            $this->err("max_id: $sql");
            return -1;
        }

        $sth->execute();
        $val = $sth->fetch(PDO::FETCH_NUM);

        return $val[0];
    }

    /*
    * Fetch all elements from a query and return the data.  Be careful you don't
    * select everyting from a huge table.
    */
    function fetch_all($sql, $values = false)
    {
        $sth = $this->dbh->prepare($sql);

        if ($sth == false) {
            $this->err("fetch_all: $sql");
            return null;
        }

        if ($values) {
            $sth->execute($this->_ensure_array($values));
        } else {
            $sth->execute();       
        }
    
        $rows = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

   function __destruct() {
		$this->dbh = null;
	}
}

?>
