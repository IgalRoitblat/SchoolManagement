<?php

class DB {
	private static $connection;	
	private function __construct() {}

	public static function getConnection () {		
		if (!self::$connection) {
			try {
				self::$connection = new PDO('mysql:dbname=school;host=localhost:8889', 'root', 'root');
				self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);     
				self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);          
				self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					die($e->getMessage());
			}
			return self::$connection;
		} else {
			return self::$connection;
		}
	}
}