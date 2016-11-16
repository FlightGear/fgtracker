<?php
///////////////////////////////////////////////////////////////////////
// REG_CALLSIGN - return: $reply["data"] =  Array("ok"=>boolean, "msg"=>String())
///////////////////////////////////////////////////////////////////////
function reg_callsign($conn,$reply,$callsign,$email,$ip,$grecaptcharesponse)
{
	global $var;
	
	$success=true;
	
	/*Write header - always 200 if reached here*/
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
	
	$data = array("secret" => $var["reCAPTCHA_secret"], "response" => $grecaptcharesponse);  
	$data = http_build_query($data);  
	$opts = array(
	'socket' => array(
        'bindto' => '0:0',
    ),
   'http'=>array(  
     'method'=>"POST",  
     'header'=>"Content-type: application/x-www-form-urlencoded\r\n".  
               "Content-length:".strlen($data)."\r\n" . 
               "\r\n",
     'content' => $data)
	 );
	$cxContext = stream_context_create($opts);
	$url="https://www.google.com/recaptcha/api/siteverify";
	$gstr= file_get_contents($url, false, $cxContext);
	if ($gstr===false)
	{
		$verifymsg="Failed to connect to reCAPTCHA server<br />";
		$success=false;
	}
	/*Expect something like { "success": true, "challenge_ts": "2016-09-21T08:44:45Z", "hostname": "mpserver15.flightgear.org" } return from server*/
	$res=json_decode($gstr, true);
	if($res['success']===false)
	{
		$success=false;
		$verifymsg="reCAPTCHA test failed<br />";
	}	
	
	/*Verify email*/
	if (!filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		$verifymsg.="Email validation failed<br />";
		$success=false;
    }
	
	/*Verify callsign*/
	if(preg_match("/^[a-zA-Z0-9\\-]+$/", $callsign) != 1) 
	{// string NOT only contain the a to z , A to Z, 0 to 9, -
		$verifymsg.="Callsign validation failed - illegal character(s).<br />";
		$success=false;
	}
	if (callsign_validation($callsign)===false)
	{
		$verifymsg.="Callsign validation failed - callsign contains illegal words<br />";
		$success=false;
	}
		
	
	if( strlen($callsign) < 1 or strlen($callsign) > 7) 
	{
		$verifymsg.="Callsign validation failed - number of characters out of range<br />";
		$success=false;
	}
	
	/*return if failed*/
	if ($success===false)
	{
		$reply["data"]=Array("ok"=>false,"msg"=>$verifymsg);
		return $reply;
	}
		
	/*check if callsign used*/	
	$callsign_check=callsign_registered($conn,$callsign);
	if ($callsign_check["ok"]===false)
	{
		$reply["data"]=Array("ok"=>false,"msg"=>$callsign_check["msg"]);
		return $reply;
	}
	
	if (!is_null($callsign_check["activation_level"]))
	{
		$reply["data"]=Array("ok"=>false,"msg"=>$callsign_check["msg"]);
		return $reply;
	}
	
	/*check if email has been used before*/
	$res=pg_query($conn,"SELECT count(*) as counter FROM callsigns WHERE email='$email';");
	if ($res===false)
	{
		$reply["data"]=Array("ok"=>false,"msg"=>"System internal error. (REG-02)");
		return $reply;
	}
	$cnt=pg_result($res,0,'counter');
	pg_free_result($res);
	
	if ($cnt>=5)
		{
		$reply["data"]=Array("ok"=>false,"msg"=>"Email has been used for callsign registration 5 times or more.");
		return $reply;
	}
	
	/*Okay, please insert*/
	$regtoken=sha1(time()+1/rand());
	$res=pg_query($conn,"INSERT INTO callsigns VALUES ('$callsign','$email','$ip','$regtoken');");
	if (!$res) 
	{
		$reply["data"]=Array("ok"=>false,"msg"=>"System internal error. (REG-03)");
		return $reply;
	}
	/*Send mail*/
	$subject="Email verification for registering callsign '$callsign' in FGTracker";
	$headers   = array();
	$headers[] = "MIME-Version: 1.0";
	$headers[] = "Content-type: text/html; charset=iso-8859-1";
	$headers[] = "From: FG Tracker <no-reply@mpserver15.flightgear.org>";
	$headers[] = "Reply-To: No reply <no-reply@mpserver15.flightgear.org>";
	$headers[] = "X-Mailer: PHP/FGTracker";

	$message="<p>Hi $callsign,</p><p>Thank you for your callsign registration in FGTracker. To complete the registration process, you are required to verify your email by clicking this link: <a href=\"http://mpserver15.flightgear.org/modules/fgtracker/?FUNCT=REGCALLSIGN3&callsign=$callsign&token=$regtoken\">http://mpserver15.flightgear.org/modules/fgtracker/?FUNCT=REGCALLSIGN3&callsign=$callsign&token=$regtoken</a>.</p><p>Please do not reply this email. All reply will not be read and will be deleted. Should you face any difficuities, plese PM hazuki in FlightGear forum.</p><p>&nbsp;<br />&nbsp;</p><p>FGTracker</p>";
	
	if(mail($email, $subject, $message, implode("\r\n", $headers)))
		$verifymsg='OK';
	else $verifymsg='OK, except failed in sending mail. Please PM hazuki in FlightGear forum for manual verification.';
	
	$reply["data"]=Array("ok"=>$success,"msg"=>$verifymsg);
		
	return $reply;
}


///////////////////////////////////////////////////////////////////////
// REG_CALLSIGN - verify email - return: $reply["data"] =  Array("ok"=>boolean, "msg"=>String())
///////////////////////////////////////////////////////////////////////
function reg_callsign2($conn,$reply,$callsign,$token)
{
	$callsign=pg_escape_string($conn,$callsign);
	$token=pg_escape_string($conn,$token);
	$res=pg_query($conn,"SELECT count(*) as counter FROM callsigns WHERE callsign='$callsign' and reg_token='$token' and activation_level=0;");
	if ($res===false)
	{
		$reply["data"]=Array("ok"=>false,"msg"=>"System internal error. (REG-04)".pg_last_error($conn));
		return $reply;
	}
	$cnt=pg_result($res,0,'counter');
	pg_free_result($res);
	
	if($cnt!=1)
	{
		$reply["data"]=Array("ok"=>false,"msg"=>"FGTracker could not find callsign '$callsign' which is under email verification.");
		return $reply;
	}
	$res=pg_query($conn,"UPDATE callsigns SET activation_level=10 WHERE callsign='$callsign'");
	if ($res===false)
	{
		$reply["data"]=Array("ok"=>false,"msg"=>"System internal error. (REG-05)");
		return $reply;
	}
	$reply["data"]=Array("ok"=>true,"msg"=>"OK");
	return $reply;
}

function callsign_registered($conn,$callsign)
{	//return Array("ok"=>(if function run without error),"activation_level"=>(null if not registered),"msg"=>"");
	$res=pg_query($conn,"SELECT email,activation_level,EXTRACT(EPOCH FROM now())-EXTRACT(EPOCH FROM reg_time) AS seconds_since_registered FROM callsigns WHERE callsign='$callsign';");
	if ($res===false)
	{
		$reply=Array("ok"=>false,"msg"=>"System internal error. (REG-01)");
		return $reply;
	}
	
	if (pg_num_rows($res)>0)
    {	/*already registered by others*/
		$email=pg_result($res,0,'email');
		$activation_level=pg_result($res,0,'activation_level');
		$seconds_since_registered=pg_result($res,0,'seconds_since_registered');
		pg_free_result($res);
		if($activation_level==0 and $seconds_since_registered>3600*24*3)
		{	/*callsign can be released*/
			$res=pg_query($conn,"DELETE FROM callsigns WHERE callsign='$callsign';");
			if ($res===false)
			{
				$reply=Array("ok"=>false,"msg"=>"System internal error. (REG-04)");
				return $reply;
			}
		}else
		{
			$reply=Array("ok"=>true,"activation_level"=>$activation_level,"msg"=>"Callsign already registered by ".maskEmail($email));
			return $reply;
		}
    }
	/*Not registered*/
	$reply=Array("ok"=>true,"activation_level"=>null,"msg"=>"Not registered");
	return $reply;
}

function callsign_validation($callsign)
{ /*return TRUE = validated and not contains bad word; FALSE = failed/contains bad word*/
	global $var;
	
	foreach ($var["callsign_blacklist"] as $blackword)
	{
		if (stripos($callsign,$blackword)!== false)
			return false;
	}
	return true;
	
}

function maskEmail($email, $minLength = 4, $maxLength = 8, $mask = "***") {
    $atPos = strrpos($email, "@");
    $name = substr($email, 0, $atPos);
    $len = strlen($name);
    $domain = substr($email, $atPos);

    if (($len / 2) < $maxLength) $maxLength = ($len / 2);

    $shortenedEmail = (($len > $minLength) ? substr($name, 0, $maxLength) : "");
    return  "{$shortenedEmail}{$mask}{$domain}";
}

?>