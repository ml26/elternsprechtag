<?php
class AdminGet extends HandlerBase
{
    function beforeRoute($f3) {
        parent::beforeRoute($f3);
        return $this->ensureAdmin($f3);
    }

    function login($f3) {
        if( !$this->isLoggedIn($f3) ) {
            Logger::Info($f3, "AdminGet.login", "Logged in as admin");
            $allSchueler = DbWrapper::getAllSchueler($f3->get('db'));
            $f3->set('COOKIE.login_code', $allSchueler[0]["login_code"]);
        }
        $f3->reroute('/');
    }
    
    function logout($f3) {
        $f3->clear('COOKIE.admin_secret');
        $_SERVER['PHP_AUTH_PW'] = 'xyz';
        Logger::Info($f3, "AdminGet.logout", "Logged out");
        $f3->reroute('/');
    }
    
    function sql($f3) {
        $f3->set('originalQuery','select 1+2');
        $f3->set('error','');
        $f3->set('sqlResult','');
        echo Template::instance()->render('sqlprompt.htm');
    }
    
    function index($f3) {
        $f3->set('location', 'admin_index');
        echo Template::instance()->render('adminIndex.htm');
    }
    
    function dumpDatabase($f3) {
        $db = $f3->get('db');
        Logger::Info($f3, "AdminGet.dumpDatabase", "Exporting the DB");
        
        $exportFileName = $f3->get('ROOT').'/exports/export_'.date('Y-m-d_H\hi\m').'.xlsx';
        ExcelImportExport::exportToExcel2007($db, $exportFileName);
        Logger::Info($f3, "AdminGet.dumpDatabase", "Export file: {$exportFileName}");
        
        if (!Web::instance()->send($exportFileName)) {
            $f3->error(404);
        }
    }
    
    function reservationsReport($f3) {
        $db = $f3->get('db');
        Logger::Info($f3, "AdminGet.reservationsReport", "Creating report...");
        $f3->set('reservations', DbWrapper::getAllReservierungenWithLehrer($db));
        echo Template::instance()->render('reservationsReport.htm');
    }
    
    function lock($f3) {
        if( $f3->exists('GET.newLockStatus') ) {
            try {
                $db = $f3->get('db');
                $newLockStatus = $f3->get('GET.newLockStatus');
                Logger::Info($f3, "AdminGet.lock", "Setting lock setting to {$newLockStatus}");
                DbWrapper::updateEinstellung($db, 'isLocked', $newLockStatus);
            } catch (Exception $e) {
                Logger::Error($f3, "AdminGet.lock", "Error: {$e->getMessage()}");
            }
        }
        $f3->reroute('/admin/index');
    }
    
    function absences($f3,$params) {
        $db = $f3->get('db');
        
        $curLehrer = DbWrapper::getLehrerById($db, $params['id']);
        $f3->set('curLehrer', $curLehrer);
        
        $allLehrer = DbWrapper::getAllLehrer($db);
        $f3->set('allLehrer', $allLehrer);
        
        $absences = DbWrapper::getSperrungenByLehrerId($db, $params['id']);
        $f3->set('absences', $absences);
        
        echo Template::instance()->render('absences.htm');
    }
}

?>
