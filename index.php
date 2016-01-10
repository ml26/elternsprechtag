<?php

$f3=require('lib/base.php');
$f3->set('UI','ui/');
$f3->set('AUTOLOAD','autoload/');
$f3->set('UPLOADS',$f3->get('tmp'));
$f3->set('db',new DB\SQL('sqlite:db/test.sqlite'));
$f3->set('log',new DB\SQL('sqlite:db/log.sqlite'));


Logger::Initialize($f3->get('log'));

//$f3->set('DEBUG',3);

//############### Set routes ################

$f3->route('GET /', 'UserGet->index');

$f3->route('GET /lehrer/@id','LehrerGet->lehrer');
$f3->route('POST /lehrer/@id/@action','LehrerPost->@action');

$f3->route('GET /@action','UserGet->@action');
$f3->route('GET /@action','UserGet->@action');
$f3->route('POST /@action','UserPost->@action');

$f3->route('GET /admin/absences/@id','AdminGet->absences');
$f3->route('POST /admin/absences/@id/@action','AdminPost->@action');
$f3->route('GET /admin/@action','AdminGet->@action');
$f3->route('POST /admin/@action','AdminPost->@action');

//############### START F3 ################

$f3->run();

?>
