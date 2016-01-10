<?php
class LehrerGet extends HandlerBase
{
	function beforeRoute($f3) {
		parent::beforeRoute($f3);
		if( !$this->isLoggedIn($f3) ) {
			$f3->reroute('/login');
		}
    }

	function lehrer($f3,$params) {
		$db = $f3->get('db');
		
		$curLehrer = DbWrapper::getLehrerById($db, $params['id']);
		$f3->set('curLehrer', $curLehrer);
	
		$allLehrer = DbWrapper::getAllLehrer($db);
		$f3->set('allLehrer', $allLehrer);
		
		$reservations = DbWrapper::getReservationsByLehrerId($db, $params['id'], $f3->get('COOKIE.user_id'));
		$f3->set('reservations', $reservations);
		
		$f3->set('isLocked', filter_var($f3->get('settings.isLocked'), FILTER_VALIDATE_BOOLEAN));
		$f3->set('curUserId', $f3->get('COOKIE.user_id'));
		
		echo Template::instance()->render('reservations.htm');
    }
}

?>
