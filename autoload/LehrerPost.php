<?php
class LehrerPost extends HandlerBase
{
    function reserve($f3, $params) {
        $lehrerId = $params['id'];
        $errorQueryString = '';
        if( $f3->exists('POST.zeitId') ) {
            try {
                $zeitId = $f3->get('POST.zeitId');
                $userId = $this->getCurrentSchuelerId($f3);
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
                    $errorQueryString = '?errorMessage=Sie haben bei diesem Lehrer bereits einen anderen Termin gebucht';
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
                $userId = $this->getCurrentSchuelerId($f3);
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
