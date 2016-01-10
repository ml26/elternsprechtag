<?php

require_once dirname(__FILE__) . '/../lib/PHPExcel/IOFactory.php';

class ExcelImportExport
{
	public static $lastExecutedStatement = null;

	public static function exportToExcel2007($db, $exportFileName) {
	
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Lechner Michael");
		$objPHPExcel->getProperties()->setLastModifiedBy("Lechner Michael");
		$objPHPExcel->getProperties()->setTitle("Datenbankexport ".date('d.m.Y H:hi:m'));
		$objPHPExcel->getProperties()->setSubject("Datenbankexport");
		$objPHPExcel->getProperties()->setDescription("Datenbankexport");
		
		$objPHPExcel->setActiveSheetIndex(0);
		$activeSheet = $objPHPExcel->getActiveSheet();
		$activeSheet->setTitle('LEHRER');
		$activeSheet->SetCellValue('A1', 'lehrer_id');
		$activeSheet->SetCellValue('B1', 'name');
		$activeSheet->SetCellValue('C1', 'raum');
		$activeSheet->getColumnDimension('B')->setAutoSize(true);
		$activeSheet->getColumnDimension('C')->setAutoSize(true);
		$rowCounter = 2;
		foreach (DbWrapper::getAllLehrer($db) as $lehrer) {
			$activeSheet->SetCellValue('A'.$rowCounter, $lehrer['lehrer_id']);
			$activeSheet->SetCellValue('B'.$rowCounter, $lehrer['name']);
			$activeSheet->SetCellValue('C'.$rowCounter, $lehrer['raum']);
			// $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCounter, json_encode($lehrer));
			$rowCounter = $rowCounter + 1;
		}
		
		$activeSheet = $objPHPExcel->createSheet();
		$activeSheet->setTitle('SCHUELER');
		$activeSheet->SetCellValue('A1', 'schueler_id');
		$activeSheet->SetCellValue('B1', 'name');
		$activeSheet->getColumnDimension('B')->setAutoSize(true);
		$rowCounter = 2;
		foreach (DbWrapper::getAllSchueler($db) as $schueler) {
			$activeSheet->SetCellValue('A'.$rowCounter, $schueler['schueler_id']);
			$activeSheet->SetCellValue('B'.$rowCounter, $schueler['name']);
			$rowCounter = $rowCounter + 1;
		}
		
		$activeSheet = $objPHPExcel->createSheet();
		$activeSheet->setTitle('ZEITEN');
		$activeSheet->SetCellValue('A1', 'zeit_id');
		$activeSheet->SetCellValue('B1', 'zeit');
		$activeSheet->getColumnDimension('B')->setAutoSize(true);
		$rowCounter = 2;
		foreach (DbWrapper::getAllZeiten($db) as $zeit) {
			$activeSheet->SetCellValue('A'.$rowCounter, $zeit['zeit_id']);
			$activeSheet->SetCellValue('B'.$rowCounter, $zeit['zeit']);
			$rowCounter = $rowCounter + 1;
		}
		
		$activeSheet = $objPHPExcel->createSheet();
		$activeSheet->setTitle('EINSTELLUNGEN');
		$activeSheet->SetCellValue('A1', 'name');
		$activeSheet->SetCellValue('B1', 'wert');
		$activeSheet->getColumnDimension('A')->setAutoSize(true);
		$activeSheet->getColumnDimension('B')->setAutoSize(true);
		$rowCounter = 2;
		foreach (DbWrapper::getAllEinstellungen($db) as $einstellung) {
			$activeSheet->SetCellValue('A'.$rowCounter, $einstellung['name']);
			$activeSheet->SetCellValue('B'.$rowCounter, $einstellung['wert']);
			$rowCounter = $rowCounter + 1;
		}
		
		$activeSheet = $objPHPExcel->createSheet();
		$activeSheet->setTitle('SPERRUNGEN');
		$activeSheet->SetCellValue('A1', 'lehrer_id');
		$activeSheet->SetCellValue('B1', 'zeit_id');
		$activeSheet->getColumnDimension('A')->setAutoSize(true);
		$activeSheet->getColumnDimension('B')->setAutoSize(true);
		$rowCounter = 2;
		foreach (DbWrapper::getAllSperrungen($db) as $sperrung) {
			$activeSheet->SetCellValue('A'.$rowCounter, $sperrung['lehrer_id']);
			$activeSheet->SetCellValue('B'.$rowCounter, $sperrung['zeit_id']);
			$rowCounter = $rowCounter + 1;
		}
		
		$activeSheet = $objPHPExcel->createSheet();
		$activeSheet->setTitle('RESERVIERUNGEN');
		$activeSheet->SetCellValue('A1', 'lehrer_id');
		$activeSheet->SetCellValue('B1', 'zeit_id');
		$activeSheet->SetCellValue('C1', 'schueler_id');
		$activeSheet->getColumnDimension('A')->setAutoSize(true);
		$activeSheet->getColumnDimension('B')->setAutoSize(true);
		$activeSheet->getColumnDimension('C')->setAutoSize(true);
		$rowCounter = 2;
		foreach (DbWrapper::getAllReservierungen($db) as $reservierung) {
			$activeSheet->SetCellValue('A'.$rowCounter, $reservierung['lehrer_id']);
			$activeSheet->SetCellValue('B'.$rowCounter, $reservierung['zeit_id']);
			$activeSheet->SetCellValue('C'.$rowCounter, $reservierung['schueler_id']);
			$rowCounter = $rowCounter + 1;
		}
		
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save($exportFileName);
	}
	
	public static function importFromExcel2007($db, $filename) {
		$objPHPExcel = PHPExcel_IOFactory::load($filename);
		
		ExcelImportExport::dropExistingTables($db);
		ExcelImportExport::createTables($db);
		
		$s1 = ExcelImportExport::importTableFromExcel($objPHPExcel, 'LEHRER');
		$s2 = ExcelImportExport::importTableFromExcel($objPHPExcel, 'ZEITEN');
		$s3 = ExcelImportExport::importTableFromExcel($objPHPExcel, 'SCHUELER');
		$s4 = ExcelImportExport::importTableFromExcel($objPHPExcel, 'SPERRUNGEN');
		$s5 = ExcelImportExport::importTableFromExcel($objPHPExcel, 'RESERVIERUNGEN');
		$s6 = ExcelImportExport::importTableFromExcel($objPHPExcel, 'EINSTELLUNGEN');
		$allInsertStatements = array_merge($s1, $s2, $s3, $s4, $s5, $s6);
		foreach ($allInsertStatements as $statement) {
			ExcelImportExport::$lastExecutedStatement = $statement;
			$db->exec($statement);
		}
		
		$temp = '';
		return $temp;
	}
	
	private static function dropExistingTables($db) {
		$db->exec("DROP TABLE IF EXISTS RESERVIERUNGEN");
		$db->exec("DROP TABLE IF EXISTS SPERRUNGEN");
		$db->exec("DROP TABLE IF EXISTS LEHRER");
		$db->exec("DROP TABLE IF EXISTS ZEITEN");
		$db->exec("DROP TABLE IF EXISTS SCHUELER");
		$db->exec("DROP TABLE IF EXISTS EINSTELLUNGEN");
	}
	
	private static function createTables($db) {
		$db->exec("
		CREATE TABLE LEHRER
		(
			lehrer_id INTEGER NOT NULL PRIMARY KEY ASC, 
			name VARCHAR(50) NOT NULL,
			raum VARCHAR(30) NOT NULL
		)");
		$db->exec("
		CREATE TABLE ZEITEN
		(
			zeit_id INTEGER NOT NULL PRIMARY KEY ASC, 
			zeit VARCHAR(10) NOT NULL
		);");
		$db->exec("
		CREATE TABLE SCHUELER
		(
			schueler_id INTEGER NOT NULL PRIMARY KEY ASC, 
			name VARCHAR(100) NOT NULL
		);");
		$db->exec("
		CREATE TABLE EINSTELLUNGEN
		(
			name VARCHAR(50) NOT NULL PRIMARY KEY,
			wert VARCHAR(1000) NOT NULL
		);");
		$db->exec("
		CREATE TABLE RESERVIERUNGEN
		(
			lehrer_id INTEGER NOT NULL,
			zeit_id INTEGER NOT NULL,
			schueler_id INTEGER NOT NULL,
			PRIMARY KEY(lehrer_id, zeit_id),
			FOREIGN KEY (lehrer_id) REFERENCES LEHRER(lehrer_id),
			FOREIGN KEY (zeit_id) REFERENCES ZEITEN(zeit_id),
			FOREIGN KEY (schueler_id) REFERENCES SCHUELER(schueler_id)
		);");
		$db->exec("
		CREATE TABLE SPERRUNGEN
		(
			lehrer_id INTEGER NOT NULL,
			zeit_id INTEGER NOT NULL,
			PRIMARY KEY (lehrer_id, zeit_id),
			FOREIGN KEY (lehrer_id) REFERENCES LEHRER(lehrer_id),
			FOREIGN KEY (zeit_id) REFERENCES ZEITEN(zeit_id)
		);");
	}
	
	private static function importTableFromExcel($objPHPExcel, $tableName) {
		$activeSheet = $objPHPExcel->getSheetByName($tableName);
		//$temp = $activeSheet->GetCell('A1')->getValue();
		
		$highestRow = $activeSheet->getHighestRow(); // e.g. 10
		$highestColumn = $activeSheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
		
		//INSERT INTO LEHRER (lehrer_id, name, raum, ) VALUES (1, 'Dupovac Carmen', '1A-Kl. 2. OG', )
		
		$insertTemplate = 'INSERT INTO ' . $tableName . ' (';
		$strFormatBuilder = '';
		for ($col = 0; $col < $highestColumnIndex; $col++) {
			$columnName = $activeSheet->getCellByColumnAndRow($col, 1)->getValue();
			if($columnName && strlen(trim($columnName)) > 0) {
				$insertTemplate = $insertTemplate . $columnName;
				if(strpos(strtolower($columnName), '_id') === false) {
					$strFormatBuilder = $strFormatBuilder . "'%s'";
				} else {
					$strFormatBuilder = $strFormatBuilder . "%d";
				}
				
				if($col < $highestColumnIndex - 1) {
					$insertTemplate = $insertTemplate . ', ';
					$strFormatBuilder = $strFormatBuilder . ', ';
				}
			} else {
				$highestColumnIndex = $col;
				
				if(ExcelImportExport::stringEndsWith($insertTemplate, ', ')) {
					$insertTemplate = substr($insertTemplate, 0, -2);
				}
				if(ExcelImportExport::stringEndsWith($strFormatBuilder, ', ')) {
					$strFormatBuilder = substr($strFormatBuilder, 0, -2);
				}
			}
		}
		$insertTemplate = $insertTemplate . ') VALUES (' . $strFormatBuilder . ')';
		
		$insertStatements = array();
		for ($row = 2; $row <=  $highestRow; ++$row) {
			$id = $activeSheet->getCellByColumnAndRow(0, $row)->getValue();
			if($id) {
				$colValues = array();
				for ($col = 0; $col < $highestColumnIndex; $col++) {
					$colValues[] = $activeSheet->getCellByColumnAndRow($col, $row)->getValue();
				}
				$colValues = ExcelImportExport::mysql_escape_mimic($colValues);
				array_unshift($colValues, $insertTemplate);
				$insertString = call_user_func_array('sprintf', $colValues);
				//$temp = $temp . '  --  '. $insertString;
				$insertStatements[] = $insertString;
			}
		}
		
		return $insertStatements;
	}
	
	private static function stringEndsWith($haystack, $needle)
	{
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}
	
	private static function mysql_escape_mimic($inp) {
		if(is_array($inp))
			return array_map(__METHOD__, $inp);

		if(!empty($inp) && is_string($inp)) {
			return str_replace(
				array("\0", 	"\n", 	"\r", 	"'", 	'"', 	"\x1a"), 
				array('\\0', 	'\\n', 	'\\r', 	"''", 	'\\"', 	'\\Z'), 
				$inp
			);
		}

		return $inp;
	} 
}


?>
