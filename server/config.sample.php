<?php
/*FGTracker server config file (For use with FGTracker server 2.1 and above)
Rename this file to "config.php" before launching FGTracker server
*/
/*variable setup*/
$var['port'] = 8000; /*Port to bind*/
$var['error_reporting_level'] = E_NOTICE; /*Set Error reporting level (E_ERROR, E_WARNING, E_NOTICE, E_ALL). Default E_NOTICE*/
$var['log_location']=dirname(__FILE__);

/*Save the received message into a file*/
$var['log_client_msg']=false;

/*Email to Admin: You must setup mail service first (i.e. PHP can send email via its mail() function)*/
$var['error_email_send']=false; /*boolen ture/false for the reception of error notification*/
$var['error_email_address']=""; /*set your email here in order to receive error notification. formatting of this string must comply with RFC 2822. */

/*Postgresql information*/
$var['postgre_conn']['host'] = ""; /*(Linux only: empty sting for using unix socket*/
$var['postgre_conn']['port'] = 5432; /*(Linux only: lgnored if using unix socket*/
$var['postgre_conn']['desc'] = "AC-VSERVER";
$var['postgre_conn']['uname'] = "fgtracker";
$var['postgre_conn']['pass'] = "fgtracker";
$var['postgre_conn']['db'] = "fgtracker";
?>