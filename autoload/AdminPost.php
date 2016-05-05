<?php
class AdminPost extends HandlerBase
{
	function beforeRoute($f3) {
		parent::beforeRoute($f3);
    }
	
	function sql($f3) {
		$query = $f3->get('POST.sqlQuery');
		Logger::Info($f3, "AdminPost.sql", "Executing: " . $query);
		$db = $f3->get('db');
		if($query) {
			$query = trim($query);
			$f3->set('originalQuery',$query);
			try {
				$f3->set('sqlResult', DbWrapper::executeMultiSql($db, $query));
				$f3->set('error','');
			} catch (Exception $e) {
				$f3->set('sqlResult','');
				$f3->set('error',$e->getMessage());
				Logger::Error($f3, "AdminPost.sql", "SQL failed: " . $e->getMessage());
			}
		} 
		echo Template::instance()->render('sqlprompt.htm');
    }

	function scriptUpload($f3) {
		$this->ensureAdmin($f3);
		$db = $f3->get('db');
		
		Logger::Info($f3, "AdminPost.scriptUpload", "Starting import...");
		
		$dummy = $f3->get('FILES');
		$uploadedFile = $dummy["sqlFile"];
		if ($uploadedFile["error"] > 0)
		{
			Logger::Error($f3, "AdminPost.scriptUpload", $uploadedFile["error"]);
			echo "Error: " . $uploadedFile["error"] . "<br>";
		} else {
			try {
				$db->beginTransaction();
				
				$importFileName = $f3->get('ROOT').'/imports/import_'.date('Y-m-d_H\hi\m').'.xlsx';
				copy($uploadedFile["tmp_name"], $importFileName);
				
				Logger::Info($f3, "AdminPost.scriptUpload", "Import file: " . $importFileName);

				$importResult = ExcelImportExport::importFromExcel2007($db, $uploadedFile["tmp_name"]);
				$db->commit();
				
				$f3->set('scriptResult', $importResult);
				Logger::Info($f3, "AdminPost.scriptUpload", "Import finished");
			} catch (Exception $e) {
				$db->rollBack();
				$err = ExcelImportExport::$lastExecutedStatement . ' :: ' . $e->getMessage();
				Logger::Error($f3, "AdminPost.scriptUpload", "Import failed: " . $err);
				$f3->set('scriptResult', $err);
			}
			echo Template::instance()->render('adminIndex.htm');
		}
	}
	
	function add($f3, $params) {
		$this->ensureAdmin($f3);
		$lehrerId = $params['id'];
		if( $f3->exists('POST.zeitId') ) {
			try {
				$zeitId = $f3->get('POST.zeitId');
				Logger::Info($f3, "AdminPost.add", "Lehrer: {$lehrerId}, Zeit: {$zeitId}");
				DbWrapper::insertSperrung($f3->get('db'), $lehrerId, $zeitId);
			} catch (Exception $e) {
				Logger::Error($f3, "AdminPost.add", "Error: {$e->getMessage()}");
			}
		}
		$f3->reroute('/admin/absences/'.$lehrerId);
    }
	
	function remove($f3, $params) {
		$this->ensureAdmin($f3);
		$lehrerId = $params['id'];
		if( $f3->exists('POST.zeitId') ) {
			try {
				$zeitId = $f3->get('POST.zeitId');
				Logger::Info($f3, "AdminPost.remove", "Lehrer: {$lehrerId}, Zeit: {$zeitId}");
				DbWrapper::deleteSperrung($f3->get('db'), $lehrerId, $zeitId);
			} catch (Exception $e) {
				Logger::Error($f3, "AdminPost.remove", "Error: {$e->getMessage()}");
			}
		}
		$f3->reroute('/admin/absences/'.$lehrerId);
    }
	
	function changePassword($f3, $params) {
		$this->ensureAdmin($f3);
		$newPassword = $f3->get('POST.password');
		Logger::Info($f3, "AdminPost.changePassword", "Changing the admin password");
		DbWrapper::updateEinstellung($f3->get('db'), 'adminPassword', $newPassword);
		$f3->reroute('/admin/index');
    }
}

?>
