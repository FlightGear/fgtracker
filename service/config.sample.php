<?php
/*variable setup*/
$var['error_reporting_level'] = E_NOTICE; /*Set Error reporting level (E_ERROR, E_WARNING, E_NOTICE, E_ALL). Default E_NOTICE*/
$var['log_location']=dirname(__FILE__);
$var['fgtracker_xoops_location']="../web/xoops_modules/fgtracker"; /*Define the dependency - FGTracker XOOPS modules here*/
$var['archive_date']="2015-12-01"; /*Define Date of flights to be archived*/

/*Postgresql information*/
$var['postgre_conn']['host'] = ""; /*(Notes for Linux user: empty sting for using unix socket)*/
$var['postgre_conn']['port'] = 5432; /*(Notes for Linux user: lgnored if using unix socket)*/
$var['postgre_conn']['desc'] = "AC-VSERVER";
$var['postgre_conn']['uname'] = "fgtracker";
$var['postgre_conn']['pass'] = "fgtracker";
$var['postgre_conn']['db'] = "fgtracker";
?>