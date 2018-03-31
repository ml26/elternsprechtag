<?php
class DbWrapper
{
	public static function isSqlStatement($question){
		return (isset($question) && trim($question)!=='');
	}

	public static function executeMultiSql($db, $sqlString) {
		$statements = array_filter(explode(';', $sqlString), 'DbWrapper::isSqlStatement');
		$result = null;
		foreach ($statements as $statement) {
			//echo "<br/>statement: ".$statement;
			$result = $db->exec($statement);
		}
		return $result;
	}
	
    public static function getSchuelerIdByName($db, $schuelerName) {
        $result = $db->exec("
			SELECT schueler_id 
			FROM SCHUELER 
			WHERE name LIKE :name", 
			array(':name'=>$schuelerName)
		);
		return $result[0]["schueler_id"];
    }
	
	public static function getSchuelerNameById($db, $schuelerId) {
        $result = $db->exec("
			SELECT name 
			FROM SCHUELER 
			WHERE schueler_id LIKE :id", 
			array(':id'=>$schuelerId)
		);
		return $result[0]["name"];
    }
	
	public static function getAllSchueler($db) {
        $result = $db->exec("
			SELECT schueler_id, name, klasse, login_code
			FROM SCHUELER 
			ORDER BY name"
		);
		return $result;
    }
	
	public static function getAllLehrer($db) {
        $result = $db->exec("
			SELECT lehrer_id, name, raum, klassen
			FROM LEHRER 
			ORDER BY name"
		);
		return $result;
    }
	
	public static function getAllZeiten($db) {
        $result = $db->exec("
			SELECT zeit_id, zeit 
			FROM ZEITEN 
			ORDER BY zeit_id"
		);
		return $result;
    }
	
	public static function getAllEinstellungen($db) {
        $result = $db->exec("
			SELECT name, wert
			FROM EINSTELLUNGEN 
			ORDER BY name"
		);
		return $result;
    }
	
	public static function getAllReservierungen($db) {
        $result = $db->exec("
			SELECT lehrer_id, schueler_id, zeit_id
			FROM RESERVIERUNGEN"
		);
		return $result;
    }
	
	public static function getAllSperrungen($db) {
        $result = $db->exec("
			SELECT lehrer_id, zeit_id
			FROM SPERRUNGEN"
		);
		return $result;
    }
	
	public static function getAllReservierungenWithLehrer($db) {
		$result = $db->exec("
			SELECT 
				L.name as 'lehrer_name',
				Z.zeit as 'zeit',
				S.name as 'schueler_name'
			FROM LEHRER L
			CROSS JOIN ZEITEN Z
			LEFT OUTER JOIN RESERVIERUNGEN R ON 
				Z.zeit_id = R.zeit_id
				AND R.lehrer_id = L.lehrer_id
			LEFT OUTER JOIN SCHUELER S ON S.schueler_id = R.schueler_id
			WHERE NOT EXISTS ( SELECT * FROM SPERRUNGEN S2 
							   WHERE S2.lehrer_id = L.lehrer_id 
							   AND S2.zeit_id = Z.zeit_id)
			ORDER BY L.lehrer_id, Z.zeit_id"
		);
		return $result;
	}
	
	public static function getLehrerById($db, $lehrerId) {
        $result = $db->exec("
			SELECT lehrer_id, name, raum
			FROM LEHRER 
			WHERE lehrer_id = :id", 
			array(':id'=>$lehrerId)
		);
		return $result[0];
    }
	
	public static function getReservationsByLehrerId($db, $lehrerId, $schuelerId) {
        $result = $db->exec("
			SELECT 
				Z.zeit_id as 'zeit_id',
				Z.zeit as 'zeit',
				R.schueler_id as 'schueler_id',
				R2.lehrer_id as 'kollision_lehrer_id',
				L2.name as 'kollision_lehrer_name',
				CASE 
				   WHEN R2.lehrer_id IS NOT NULL THEN 'kollision'
				   WHEN R.schueler_id = :schuelerId THEN 'reserviert'
				   WHEN R.schueler_id IS NOT NULL THEN 'vergeben'
				   ELSE 'frei'
				END as 'status'
			FROM ZEITEN Z
			LEFT OUTER JOIN RESERVIERUNGEN R ON 
				Z.zeit_id = R.zeit_id 
				AND R.lehrer_id = :lehrerId
			LEFT OUTER JOIN RESERVIERUNGEN R2 ON 
				R2.zeit_id = Z.zeit_id 
				AND (R2.lehrer_id!=R.lehrer_id OR R.lehrer_id is null)
				AND R2.schueler_id= :schuelerId
			LEFT OUTER JOIN LEHRER L2 ON L2.lehrer_id = R2.lehrer_id
			WHERE NOT EXISTS ( SELECT * 
							   FROM SPERRUNGEN S 
							   WHERE S.lehrer_id = :lehrerId 
							   AND S.zeit_id = Z.zeit_id)", 
			array(':lehrerId'=>$lehrerId, ':schuelerId'=>$schuelerId)
		);
		return $result;
    }

	public static function getReservationsByUserId($db, $schuelerId) {
		$result = $db->exec("
			SELECT
				Z.zeit_id,
				Z.zeit,
				L.name as 'lehrer_name',
				L.raum
			FROM RESERVIERUNGEN R 
			JOIN ZEITEN Z ON Z.zeit_id = R.zeit_id
			JOIN LEHRER L ON L.lehrer_id = R.lehrer_id
			WHERE R.schueler_id = :schuelerId
			ORDER BY Z.zeit", 
			array(':schuelerId'=>$schuelerId)
		);
		return $result;
	}
	
	public static function getSperrungenByLehrerId($db, $lehrerId) {
		$result = $db->exec("
			SELECT 
				Z.zeit_id as 'zeit_id',
				Z.zeit as 'zeit',
				CASE WHEN S.lehrer_id IS NULL THEN 0 ELSE 1 END as 'is_absent'
			FROM Lehrer L
			CROSS JOIN Zeiten Z
			LEFT OUTER JOIN Sperrungen S ON 
				S.lehrer_id = L.lehrer_id 
				AND S.zeit_id = Z.zeit_id
			WHERE L.lehrer_id = :lehrerId
			ORDER BY Z.zeit_id", 
			array(':lehrerId'=>$lehrerId)
		);
		return $result;
	}
	
	public static function getLehrerBySchuelerId($db, $schuelerId) {
		$result = $db->exec("
			SELECT L.lehrer_id, L.name, L.raum
			FROM lehrer L
			JOIN lehrer_klassen LK ON LK.lehrer_id = L.lehrer_id
			JOIN schueler S ON S.schueler_id = :schueler_id AND S.klasse = LK.klasse
			ORDER BY L.name", 
			array(':schueler_id'=>$schuelerId)
		);
		return $result;
	}
	
	
	public static function insertReservation($db, $lehrerId, $zeitId, $schuelerId) {
		$result = $db->exec("
			INSERT INTO RESERVIERUNGEN (lehrer_id, zeit_id, schueler_id)
			VALUES (:lehrerId, :zeitId, :schuelerId)", 
			array(':lehrerId'=>$lehrerId, ':zeitId'=>$zeitId, ':schuelerId'=>$schuelerId)
		);
		return $result ? true : false;
	}
	
	public static function insertSperrung($db, $lehrerId, $zeitId) {
		$result = $db->exec("
			INSERT INTO SPERRUNGEN (lehrer_id, zeit_id)
			VALUES (:lehrerId, :zeitId)", 
			array(':lehrerId'=>$lehrerId, ':zeitId'=>$zeitId)
		);
		return $result ? true : false;
	}
	
	public static function deleteReservation($db, $lehrerId, $zeitId, $schuelerId) {
		$result = $db->exec("
			DELETE FROM RESERVIERUNGEN 
			WHERE lehrer_id = :lehrerId 
			AND zeit_id = :zeitId
			AND schueler_id = :schuelerId", 
			array(':lehrerId'=>$lehrerId, ':zeitId'=>$zeitId, ':schuelerId'=>$schuelerId)
		);
		return $result ? true : false;
	}
	
	public static function deleteSperrung($db, $lehrerId, $zeitId) {
		$result = $db->exec("
			DELETE FROM SPERRUNGEN 
			WHERE lehrer_id = :lehrerId 
			AND zeit_id = :zeitId", 
			array(':lehrerId'=>$lehrerId, ':zeitId'=>$zeitId)
		);
		return $result ? true : false;
	}
	
	public static function updateEinstellung($db, $name, $wert) {
		$result = $db->exec("
			UPDATE EINSTELLUNGEN 
			SET wert= :wert 
			WHERE name= :name", 
			array(':wert'=>$wert, ':name'=>$name)
		);
	}
	
}

?>
