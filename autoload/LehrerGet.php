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
        
        $f3->set('location', 'lehrer_reserve');
        
        $curSchuelerId = $this->getCurrentSchuelerId($f3);
        $curLehrerId = $params['id'];
        
        $curLehrer = DbWrapper::getLehrerById($db, $curLehrerId);
        $f3->set('curLehrer', $curLehrer);
    
        $allLehrer = DbWrapper::getLehrerBySchuelerId($db, $curSchuelerId);
        $f3->set('allLehrer', $allLehrer);
        
        $reservations = DbWrapper::getReservationsByLehrerId($db, $curLehrerId, $curSchuelerId);
        $f3->set('reservations', $reservations);
        
        $f3->set('isLocked', filter_var($f3->get('settings.isLocked'), FILTER_VALIDATE_BOOLEAN));
        $f3->set('curUserId', $curSchuelerId);
        $f3->set('error', $f3->get('GET.errorMessage'));
        
        echo Template::instance()->render('reservations.htm');
    }
}

?>
