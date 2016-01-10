<?php
class UserPost extends HandlerBase
{
	function login($f3) {
		if( $f3->exists('POST.schueler_name') ) {
			$schuelerName = $f3->get('POST.schueler_name');
			try {
				$schuelerId = DbWrapper::getSchuelerIdByName($f3->get('db'), $schuelerName);
				Logger::Info($f3, "UserPost.login", "Name: {$schuelerName}, Id: {$schuelerId}");
				$f3->set('COOKIE.user_id', $schuelerId);
				$f3->set('COOKIE.user_name', $schuelerName);
				$f3->reroute('/');
			} catch (Exception $e) {
				Logger::Error($f3, "UserPost.login", 
					"Name: {$schuelerName}, Error: {$e->getMessage()}");
				$f3->reroute('/login');
			}
		} else {
			$f3->reroute('/login');
		}
    }
}

?>
