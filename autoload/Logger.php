<?php

class Logger
{
	public static function Info($f3, $source, $text) {
		Logger::Write($f3->get('log'), $source, $text, 'I');
	}
	
	public static function Warn($f3, $source, $text) {
		Logger::Write($f3->get('log'), $source, $text, 'W');
	}
	
	public static function Error($f3, $source, $text) {
		Logger::Write($f3->get('log'), $source, $text, 'E');
	}
	
	private static function Write($logDb, $source, $text, $type) {
		//try {
			// $source = SQLite3::escapeString($source);
			// $type = SQLite3::escapeString($type);
			// $text = SQLite3::escapeString(substr($text, 0, 254));
			// $stmt ="INSERT INTO LOGS (type, source, message) 
					// VALUES ('{$type}', '{$source}', '{$text}')";
			// $logDb->exec($stmt);
			$stmt = $logDb->prepare("INSERT INTO LOGS (type, source, message) VALUES (:type, :source, :message)");
			$stmt->bindValue(':message', substr($text, 0, 254));
			$stmt->bindValue(':type', $type);
			$stmt->bindValue(':source', $source);
			$stmt->execute();
		//} catch (Exception $e) {
			//ignore
		//}
	}
	
	public static function Initialize($logDb) {
		$logDb->exec("
		CREATE TABLE IF NOT EXISTS LOGS
		(
			log_id INTEGER PRIMARY KEY AUTOINCREMENT, 
			timestamp DATE DEFAULT (datetime('now','localtime')),
			type CHARACTER(1) NOT NULL,
			source VARCHAR(32) NOT NULL,
			message VARCHAR(255) NOT NULL
		)");
	}
}


?>
