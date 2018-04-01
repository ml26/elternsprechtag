<?php

//TODO: add gdpr disclaimer

$f3=require('lib/base.php');
$f3->set('UI','ui/');
$f3->set('AUTOLOAD','autoload/');
$f3->set('UPLOADS',$f3->get('tmp'));

$dbFile = 'db/data.sqlite';
$isNewDb = !file_exists($dbFile) || filesize($dbFile) === 0;
$f3->set('db',new DB\SQL('sqlite:'.$dbFile));
if ($isNewDb) {
	ExcelImportExport::importFromExcel2007($f3->get('db'), 'db/bootstrap.xlsx');
}


Logger::Initialize($f3);

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
