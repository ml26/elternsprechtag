<?php
class UserPost extends HandlerBase
{
    function login($f3) {
        if( $f3->exists('POST.login_code') ) {
            $login_code = $f3->get('POST.login_code');
            try {
                $schuelerId = DbWrapper::getSchuelerIdByLoginCode($f3->get('db'), $login_code);
                Logger::Info($f3, "UserPost.login", "Code: {$login_code}, Id: {$schuelerId}");
                $f3->set('COOKIE.user_id', $schuelerId);
                $f3->reroute('/');
            } catch (Exception $e) {
                Logger::Error($f3, "UserPost.login", 
                    "Code: {$login_code}, Error: {$e->getMessage()}");
                $f3->reroute('/login');
            }
        } else {
            $f3->reroute('/login');
        }
    }
}

?>
