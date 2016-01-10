<?php
class LehrerPost extends HandlerBase
{
	function reserve($f3, $params) {
		$lehrerId = $params['id'];
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
			}
		}
		$f3->reroute('/lehrer/'.$lehrerId);
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
