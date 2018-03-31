<?php
class UserGet extends HandlerBase
{
    function index($f3) {
        if( $this->isLoggedIn($f3) ) {
            echo Template::instance()->render('index.htm');
        } else {
            $f3->reroute('/login');
        }
    }
    
    function admin($f3) {
        $f3->reroute('/admin/index');
    }
    
    function login($f3) {
        if( $this->isLoggedIn($f3) ) {
            $f3->reroute('/');
        } else {
            echo Template::instance()->render('login.htm');
        }
    }
    
    function logout($f3) {
        if( $this->isLoggedIn($f3) ) {
            $loginCode = $f3->get('COOKIE.login_code');
            Logger::Info($f3, "UserGet.logout", "Code: {$loginCode}");
            $f3->clear('COOKIE.login_code');
        }
        $f3->reroute('/login');
    }
    
    function impressum($f3) {
        if( $this->isLoggedIn($f3) ) {
            $f3->set('location', 'impressum');
            echo Template::instance()->render('impressum.htm');
        } else {
            $f3->reroute('/login');
        }
    }
    
    function reservierungen($f3) {
        if( $this->isLoggedIn($f3) ) {
            $f3->set('location', 'user_summary');
            
            $db = $f3->get('db');
            $curSchuelerId = $this->getCurrentSchuelerId($f3);
            $f3->set('schuelerName', DbWrapper::getSchuelerNameById($db, $curSchuelerId));
            $f3->set('reservations', DbWrapper::getReservationsByUserId($db, $curSchuelerId));
            echo Template::instance()->render('userSummary.htm');
        } else {
            $f3->reroute('/login');
        }
    }
}

?>
