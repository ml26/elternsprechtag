<?php
class HandlerBase
{
	function beforeRoute($f3) {
		$this->getSettings($f3);
		$this->setAdminVar($f3);
		$f3->set('varExists',
			function($varName) use ($f3) {
				return $f3->exists($varName);
			}
		);
		$f3->set('location', 'home');
    }

    function afterRoute($f3) {
    }
	
	function setAdminVar($f3) {
		if ($f3->exists('COOKIE.admin_secret') && $f3->get('COOKIE.admin_secret') === $this->getAdminSecret($f3)) {
			$f3->set('isAdmin', true);
		} else {
			$f3->set('isAdmin', false);
		}
	}

	function ensureAdmin($f3) {
		$adminSecret = $this->getAdminSecret($f3);
		if ($f3->exists('COOKIE.admin_secret') && $f3->get('COOKIE.admin_secret') === $adminSecret) {
			$f3->set('isAdmin', true);
			return true;
		} else {
			$user = "";
			if(isset($_SERVER['PHP_AUTH_USER'])) {
				$user = $_SERVER['PHP_AUTH_USER'];
			}
			$password = "";
			if(isset($_SERVER['PHP_AUTH_PW'])) {
				$password = $_SERVER['PHP_AUTH_PW'];
			}
			$source = "unknown";
			if(isset($_SERVER['REMOTE_ADDR'])) {
				$source = $_SERVER['REMOTE_ADDR'];
			}
			Logger::Info($f3, "HandlerBase.ensureAdmin",
				"Login attempt with user '{$user}', pass '{$password}' from '{$source}'");
			
			if($user == 'admin' && $password == $f3->get('settings.adminPassword') ) {
				$f3->set('COOKIE.admin_secret', $adminSecret);
				$f3->set('isAdmin', true);
				return true;
			} else {
				
				Logger::Warn($f3, "HandlerBase.ensureAdmin", 
					"Root access denied to IP {$_SERVER['REMOTE_ADDR']}");
			
				header('WWW-Authenticate: Basic realm="Elternsprechtag St. Rupert"');
				header('HTTP/1.0 401 Unauthorized');
				echo "You must enter a valid login ID and password to access this resource\n";
				$f3->set('isAdmin', false);
				return false;
			}
		}
	}
	
	function getAdminSecret($f3) {
		$adminPassword = $f3->get('settings.adminPassword') || '123456';
		return hash('sha256', 'mySaltySalt'.$adminPassword);
	}

	function isLoggedIn($f3) {
		if( $f3->exists('COOKIE.user_id') ) {
			return true;
		} else {
			return false;
		}
	}
	
	function getCurrentSchuelerName($f3) {
		if( $f3->exists('COOKIE.user_name') && $f3->get('COOKIE.user_name') ) {
			return $f3->get('COOKIE.user_name');
		} else {
			$schuelerName = DbWrapper::getSchuelerNameById($f3->get('db'), $f3->get('COOKIE.user_id'));
			$f3->set('COOKIE.user_name', $schuelerName);
			return $schuelerName;
		}
	}
	
	function getSettings($f3) {
		$db = $f3->get('db');
		$dbResults = DbWrapper::getAllEinstellungen($db);
		$settings = array();
		foreach($dbResults as $setting) {
			$settings[$setting['name']] = $setting['wert'];
		}
		$f3->set('settings', $settings);
	}
}

?>
