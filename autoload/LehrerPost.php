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
                Logger::Error($f3, "LehrerPost.reserve", "Lehrer: {$lehrerId}, Error: {$e->getMessage()}");
                $errorQueryString = $this->buildErrorMessage($e);
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
                Logger::Error($f3, "LehrerPost.release", "Lehrer: {$lehrerId}, Error: {$e->getMessage()}");
            }
        }
        $f3->reroute('/lehrer/'.$lehrerId);
    }
    
    function buildErrorMessage($e) {
        if ($this->messageContains($e, 'UNIQUE constraint failed') && $this->messageContains($e, 'lehrer_id')) {
            if ($this->messageContains($e, 'zeit_id')) {
                return '?errorMessage=Dieser Termin ist bereits reserviert';
            } else if ($this->messageContains($e, 'schueler_id')) {
                return '?errorMessage=Sie haben bei diesem Lehrer bereits einen anderen Termin gebucht';
            }
        }
        return '?errorMessage=Reservierung konnte nicht durchgeführt werden';
    }
    
    function messageContains($e, $str) {
        return strpos($e->getMessage(), $str) !== false;
    }
}

?>
