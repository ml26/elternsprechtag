<?php
class LehrerPost extends HandlerBase
{
	function reserve($f3, $params) {
		$lehrerId = $params['id'];
		$errorQueryString = '';
		if( $f3->exists('POST.zeitId') ) {
			try {
				$zeitId = $f3->get('POST.zeitId');
				$userId = $f3->get('COOKIE.user_id');
				Logger::Info($f3, "LehrerPost.reserve", 
					"Lehrer: {$lehrerId}, Zeit: {$zeitId}, User: {$userId}");
				DbWrapper::insertReservation(
					$f3->get('db'), 
					$lehrerId,
					$zeitId,
					$userId
				);
			} catch (Exception $e) {
				Logger::Error($f3, "LehrerPost.reserve", 
					"Lehrer: {$lehrerId}, Error: {$e->getMessage()}");
					
				if (strpos($e->getMessage(), 'PDOStatement: UNIQUE constraint failed') !== false) {
					$errorQueryString = '?errorMessage=Nur eine Reservierung pro Lehrer möglich';
				} else {
					$errorQueryString = '?errorMessage=Reservierung konnte nicht durchgeführt werden';
				}
			}
		}
		$f3->reroute('/lehrer/'.$lehrerId.$errorQueryString);
    }
	
	function release($f3, $params) {
		$lehrerId = $params['id'];
		if( $f3->exists('POST.zeitId') ) {
			try {
				$zeitId = $f3->get('POST.zeitId');
				$userId = $f3->get('COOKIE.user_id');
				Logger::Info($f3, "LehrerPost.release", 
					"Lehrer: {$lehrerId}, Zeit: {$zeitId}, User: {$userId}");
				DbWrapper::deleteReservation(
					$f3->get('db'), 
					$lehrerId,
					$zeitId,
					$userId
				);
			} catch (Exception $e) {
				Logger::Error($f3, "LehrerPost.release", 
					"Lehrer: {$lehrerId}, Error: {$e->getMessage()}");
			}
		}
		$f3->reroute('/lehrer/'.$lehrerId);
    }
}

?>
