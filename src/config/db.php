<?php
require_once ('../vendor/thingengineer/mysqli-database-class/MysqliDb.php');

class db{

	private $dbhost='localhost';
	private $dbuser='root';
	private $dbpass='esferaSS@370';
	private $dbname='Insurance_db';

	//connect db	
	public function connect(){
		$dbconnection = new MysqliDb ($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
		if(!$dbconnection){
			die('errrr connn');
		}
		return $dbconnection;
	}
}