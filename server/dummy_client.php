<?php
/*Dummy client virtualizing NOWAIT protocal*/

/* Check abnormal messages
$msgArray=Array(
	"FIRST LINE to be ignored",
	"NOWAIT",
	"CONNECT AF2222 test A340-600HGW 2015-11-22 04:37:00",
	"POSITION AF2222 test 45.724361 5.082576 798.709855 2015-11-22 04:37:01",
	"POSITION AF2222 test 45.724361 -5.082576 798.709855 2015-11-22 04:37:11",
	"POSITION AF2222 test 45.724361 5.082576 798.709855 2015-11-22 04:37:21",
	"POSITION AF2222 test 45.724361 5.082576 -798.709855 2015-11-22 04:37:31",
	"POSITION * Bad Client *  0 0 . 2012-12-03 21:03:53",
	"POSITION AF2222 test 45.724361 5.082576 798.709855 2015-11-22 04:37:41",
	"POSITION AF2222 test  . . . 2012-12-08 14:28:42",
	"DISCONNECT AF2222 test A340-600HGW 2015-11-22 04:37:50",
	"PING");*/

/* Check separated flight */
$msgArray=Array(
	"FIRST LINE to be ignored",
	"NOWAIT"/*,
	"CONNECT Quofei test B777 2012-06-23 22:16:25",
	"POSITION Quofei test 44.943923 -73.097986 234.202738 2012-06-23 22:16:27",
	"POSITION Quofei test 44.943923 -73.097986 234.425422 2012-06-23 22:16:47",
	"POSITION Quofei test 44.943923 -73.097987 234.421441 2012-06-23 22:16:57",
	"POSITION Quofei test 44.943923 -73.097987 234.417437 2012-06-23 22:17:07",
	"POSITION Quofei test 44.943923 -73.097988 234.413432 2012-06-23 22:17:17",
	"POSITION Quofei test 44.943923 -73.097988 234.409452 2012-06-23 22:17:27",
	"POSITION Quofei test 44.943923 -73.097989 234.405447 2012-06-23 22:17:37",
	"POSITION Quofei test 44.943923 -73.097989 234.401467 2012-06-23 22:17:47",
	"POSITION Quofei test 44.94392 -73.097989 234.375861 2012-06-23 22:17:57",
	"POSITION Quofei test 44.943917 -73.097989 234.371881 2012-06-23 22:18:07",
	"POSITION Quofei test 44.943916 -73.09799 234.378188 2012-06-23 22:18:17",
	"POSITION Quofei test 44.943915 -73.09799 234.536902 2012-06-23 22:18:27",
	"POSITION Quofei test 44.943914 -73.09799 234.542591 2012-06-23 22:18:37",
	"POSITION Quofei test 44.943896 -73.097988 234.545824 2012-06-23 22:18:47",
	"POSITION Quofei test 44.942086 -73.097722 232.276879 2012-06-23 22:18:57",
	"POSITION Quofei test 44.937436 -73.09705 235.078296 2012-06-23 22:19:07",
	"POSITION Quofei test 44.930887 -73.096095 512.078771 2012-06-23 22:19:17",
	"POSITION Quofei test 44.92376 -73.095258 912.673268 2012-06-23 22:19:27",
	"POSITION Quofei test 44.916071 -73.094486 1385.637495 2012-06-23 22:19:37",
	"POSITION Quofei test 44.90768 -73.093632 1792.667911 2012-06-23 22:19:47",
	"POSITION Quofei test 44.89815 -73.092555 2270.373769 2012-06-23 22:19:57",
	"POSITION Quofei test 44.88819 -73.091535 2937.307312 2012-06-23 22:20:07",
	"POSITION Quofei test 44.877382 -73.090235 3522.051808 2012-06-23 22:20:17",
	"POSITION Quofei test 44.865384 -73.088979 3338.571394 2012-06-23 22:20:27",
	"POSITION Quofei test 44.851425 -73.093403 3421.298335 2012-06-23 22:20:37",
	"POSITION Quofei test 44.839204 -73.103443 4743.467511 2012-06-23 22:20:47",
	"POSITION Quofei test 44.827424 -73.11255 6006.033066 2012-06-23 22:20:57",
	"POSITION Quofei test 44.81546 -73.120559 6709.600054 2012-06-23 22:21:07",
	"POSITION Quofei test 44.80225 -73.13055 6276.288681 2012-06-23 22:21:17",
	"POSITION Quofei test 44.790797 -73.147602 6129.358137 2012-06-23 22:21:27",
	"POSITION Quofei test 44.788871 -73.171339 6437.337381 2012-06-23 22:21:37",
	"POSITION Quofei test 44.789582 -73.192639 8569.324079 2012-06-23 22:21:47",
	"POSITION Quofei test 44.790702 -73.212602 9961.6581 2012-06-23 22:21:57",
	"POSITION Quofei test 44.792359 -73.232743 10573.86758 2012-06-23 22:22:07",
	"POSITION Quofei test 44.794058 -73.25349 11000.8831 2012-06-23 22:22:17",
	"POSITION Quofei test 44.797489 -73.275654 10623.69983 2012-06-23 22:22:27",
	"POSITION Quofei test 44.807813 -73.294533 10445.91901 2012-06-23 22:22:37",
	"POSITION Quofei test 44.821267 -73.3099 11269.78522 2012-06-23 22:22:47",
	"POSITION Quofei test 44.834824 -73.324672 11426.70242 2012-06-23 22:22:57",
	"POSITION Quofei test 44.850432 -73.337811 11109.55392 2012-06-23 22:23:07",
	"POSITION Quofei test 44.866922 -73.348913 11344.32166 2012-06-23 22:23:17",
	"POSITION Quofei test 44.882946 -73.359881 11571.67588 2012-06-23 22:23:27",
	"POSITION Quofei test 44.899016 -73.368604 11421.70337 2012-06-23 22:23:37",
	"POSITION Quofei test 44.916088 -73.371544 11096.43844 2012-06-23 22:23:47",
	"POSITION Quofei test 44.932825 -73.372263 11135.6958 2012-06-23 22:23:57",
	"POSITION Quofei test 44.94911 -73.372867 11274.95257 2012-06-23 22:24:07",
	"POSITION Quofei test 44.964606 -73.372855 11459.31976 2012-06-23 22:24:17",
	"POSITION Quofei test 44.979567 -73.371448 11280.66845 2012-06-23 22:24:27",
	"POSITION Quofei test 44.993933 -73.370344 11437.82217 2012-06-23 22:24:37",
	"POSITION Quofei test 45.007483 -73.370737 11639.55969 2012-06-23 22:24:47",
	"POSITION Quofei test 45.020249 -73.371912 11795.70225 2012-06-23 22:24:57",
	"DISCONNECT Quofei test B777 2012-06-23 22:24:59",
	/*"POSITION Quofei test 45.032277 -73.37152 12042.6911 2012-06-23 22:25:07",
	"POSITION Quofei test 45.044122 -73.370445 11777.54591 2012-06-23 22:25:17",
	"POSITION Quofei test 45.056108 -73.370483 11568.34163 2012-06-23 22:25:27",
	"POSITION Quofei test 45.067884 -73.369743 11801.97904 2012-06-23 22:25:37",
	"POSITION Quofei test 45.079166 -73.368217 12100.25364 2012-06-23 22:25:47",
	"POSITION Quofei test 45.090602 -73.366656 11851.31497 2012-06-23 22:25:57",/
	"CONNECT Quofei test B777 2012-06-23 22:26:05",
	"POSITION Quofei test 45.102261 -73.364654 11622.85805 2012-06-23 22:26:07",
	"POSITION Quofei test 45.113836 -73.365151 11629.69913 2012-06-23 22:26:17",
	"POSITION Quofei test 45.124945 -73.367473 11793.96467 2012-06-23 22:26:27",
	"POSITION Quofei test 45.136012 -73.369297 11875.08038 2012-06-23 22:26:37",
	"POSITION Quofei test 45.147069 -73.369217 11764.50838 2012-06-23 22:26:47",
	"POSITION Quofei test 45.158024 -73.368242 11752.09521 2012-06-23 22:26:57",
	"POSITION Quofei test 45.168901 -73.368588 11833.12042 2012-06-23 22:27:07",
	"POSITION Quofei test 45.179992 -73.369419 11353.88016 2012-06-23 22:27:17",
	"POSITION Quofei test 45.19172 -73.370265 10768.88904 2012-06-23 22:27:27",
	"POSITION Quofei test 45.203737 -73.37003 10379.2111 2012-06-23 22:27:37",
	"POSITION Quofei test 45.215834 -73.369349 10040.6034 2012-06-23 22:27:47",
	"POSITION Quofei test 45.227978 -73.369941 9422.539064 2012-06-23 22:27:57",
	"POSITION Quofei test 45.240841 -73.369105 8406.105757 2012-06-23 22:28:07",
	"POSITION Quofei test 45.254407 -73.365166 7500.216297 2012-06-23 22:28:17",
	"POSITION Quofei test 45.267893 -73.359033 6927.779925 2012-06-23 22:28:27",
	"POSITION Quofei test 45.281352 -73.351553 6377.96598 2012-06-23 22:28:37",
	"POSITION Quofei test 45.294267 -73.343368 6064.755905 2012-06-23 22:28:47",
	"POSITION Quofei test 45.306926 -73.33613 5964.704381 2012-06-23 22:28:57",
	"POSITION Quofei test 45.319283 -73.330165 5746.403243 2012-06-23 22:29:07",
	"POSITION Quofei test 45.331514 -73.324915 5424.612525 2012-06-23 22:29:17",
	"POSITION Quofei test 45.343666 -73.320417 5123.312701 2012-06-23 22:29:27",
	"POSITION Quofei test 45.355678 -73.316433 4848.739701 2012-06-23 22:29:37",
	"POSITION Quofei test 45.367427 -73.31266 4695.183734 2012-06-23 22:29:47",
	"POSITION Quofei test 45.378748 -73.309021 4555.646435 2012-06-23 22:29:57",
	"POSITION Quofei test 45.389762 -73.307544 4596.223899 2012-06-23 22:30:07",
	"POSITION Quofei test 45.400377 -73.309984 4624.962124 2012-06-23 22:30:17",
	"POSITION Quofei test 45.41062 -73.314243 4525.451978 2012-06-23 22:30:27",
	"POSITION Quofei test 45.420729 -73.318943 4394.5849 2012-06-23 22:30:37",
	"POSITION Quofei test 45.430807 -73.323803 4305.726027 2012-06-23 22:30:47",
	"POSITION Quofei test 45.440673 -73.328598 4311.273657 2012-06-23 22:30:57",
	"POSITION Quofei test 45.450345 -73.33307 4231.204004 2012-06-23 22:31:07",
	"POSITION Quofei test 45.460271 -73.337349 4052.093409 2012-06-23 22:31:17",
	"POSITION Quofei test 45.470118 -73.341342 3905.318839 2012-06-23 22:31:27",
	"POSITION Quofei test 45.480306 -73.344507 3716.249302 2012-06-23 22:31:37",
	"POSITION Quofei test 45.490729 -73.347443 3594.228037 2012-06-23 22:31:47",
	"POSITION Quofei test 45.500973 -73.350647 3449.93007 2012-06-23 22:31:57",
	"POSITION Quofei test 45.511507 -73.353801 3060.428713 2012-06-23 22:32:07",
	"POSITION Quofei test 45.522503 -73.356549 2547.930675 2012-06-23 22:32:17",
	"POSITION Quofei test 45.533615 -73.360161 2143.928528 2012-06-23 22:32:27",
	"POSITION Quofei test 45.542088 -73.371301 1861.53318 2012-06-23 22:32:37",
	"POSITION Quofei test 45.54429 -73.387739 1405.308263 2012-06-23 22:32:47",
	"POSITION Quofei test 45.541734 -73.403923 1424.866913 2012-06-23 22:32:57",
	"POSITION Quofei test 45.545277 -73.417118 1810.293356 2012-06-23 22:33:07",
	"POSITION Quofei test 45.551441 -73.429695 1634.926106 2012-06-23 22:33:17",
	"POSITION Quofei test 45.557597 -73.443038 1532.170018 2012-06-23 22:33:27",
	"POSITION Quofei test 45.563022 -73.45596 1706.087413 2012-06-23 22:33:37",
	"POSITION Quofei test 45.568167 -73.468255 1807.794904 2012-06-23 22:33:47",
	"POSITION Quofei test 45.573115 -73.480148 1863.616692 2012-06-23 22:33:57",
	"POSITION Quofei test 45.577671 -73.492167 1826.013623 2012-06-23 22:34:07",
	"POSITION Quofei test 45.579591 -73.505448 1856.387937 2012-06-23 22:34:17",
	"POSITION Quofei test 45.578522 -73.518873 1835.85858 2012-06-23 22:34:27",
	"POSITION Quofei test 45.577207 -73.532227 1806.053221 2012-06-23 22:34:37",
	"POSITION Quofei test 45.575918 -73.545412 1750.418613 2012-06-23 22:34:47",
	"POSITION Quofei test 45.574642 -73.557748 1778.112879 2012-06-23 22:34:57",
	"POSITION Quofei test 45.573227 -73.569056 1790.476937 2012-06-23 22:35:07",
	"POSITION Quofei test 45.571668 -73.579882 1746.847887 2012-06-23 22:35:17",
	"POSITION Quofei test 45.569926 -73.590654 1688.291076 2012-06-23 22:35:27",
	"POSITION Quofei test 45.568097 -73.601415 1642.306958 2012-06-23 22:35:37",
	"POSITION Quofei test 45.566266 -73.61199 1583.78965 2012-06-23 22:35:47",
	"POSITION Quofei test 45.564439 -73.622604 1515.192299 2012-06-23 22:35:57",
	"POSITION Quofei test 45.56114 -73.632505 1388.497655 2012-06-23 22:36:07",
	"POSITION Quofei test 45.556075 -73.641252 1291.028194 2012-06-23 22:36:17",
	"POSITION Quofei test 45.550547 -73.649259 1264.08774 2012-06-23 22:36:27",
	"POSITION Quofei test 45.544848 -73.656832 1226.632992 2012-06-23 22:36:37",
	"POSITION Quofei test 45.539078 -73.664142 1185.399664 2012-06-23 22:36:47",
	"POSITION Quofei test 45.533379 -73.671562 1116.615074 2012-06-23 22:36:57",
	"POSITION Quofei test 45.52766 -73.678946 1067.897246 2012-06-23 22:37:07",
	"POSITION Quofei test 45.521826 -73.686183 999.565057 2012-06-23 22:37:17",
	"POSITION Quofei test 45.516093 -73.693512 976.310975 2012-06-23 22:37:27",
	"POSITION Quofei test 45.510438 -73.701034 903.446668 2012-06-23 22:37:37",
	"POSITION Quofei test 45.504591 -73.708553 766.398342 2012-06-23 22:37:47",
	"POSITION Quofei test 45.498793 -73.716175 586.774925 2012-06-23 22:37:57",
	"POSITION Quofei test 45.493203 -73.723702 402.003258 2012-06-23 22:38:07",
	"POSITION Quofei test 45.487812 -73.730845 293.062822 2012-06-23 22:38:17",
	"POSITION Quofei test 45.482465 -73.737877 158.712897 2012-06-23 22:38:27",
	"POSITION Quofei test 45.477539 -73.744376 117.591569 2012-06-23 22:38:37",
	"POSITION Quofei test 45.473735 -73.749192 114.507428 2012-06-23 22:38:47",
	"POSITION Quofei test 45.471309 -73.752417 112.672666 2012-06-23 22:38:57",
	"POSITION Quofei test 45.470074 -73.754063 111.875109 2012-06-23 22:39:07",
	"POSITION Quofei test 45.469084 -73.755292 111.291496 2012-06-23 22:39:17",
	"POSITION Quofei test 45.468146 -73.755991 110.782465 2012-06-23 22:39:27",
	"POSITION Quofei test 45.467245 -73.756254 110.37219 2012-06-23 22:39:37",
	"POSITION Quofei test 45.466481 -73.756446 110.046136 2012-06-23 22:39:47",
	"POSITION Quofei test 45.466399 -73.756468 110.012213 2012-06-23 22:39:57",
	"POSITION Quofei test 45.466399 -73.756468 110.012213 2012-06-23 22:40:07",
	"DISCONNECT Quofei test B777 2012-06-23 22:40:09",
	"PING"*/);
function strToHex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strToUpper($hex);
}

$socket=socket_create ( AF_INET , SOCK_STREAM , SOL_TCP );
if ($socket===false)
	die("could not create socket\n");
socket_connect ( $socket , "127.0.0.1",8000);

foreach ($msgArray AS $msg)
{
	$msg.="\0";
	print "SEND: $msg\n";
	socket_send($socket, $msg, strLen($msg), 0);
	if($msg!="FIRST LINE to be ignored\0")
	{
		$reply=socket_read ( $socket , 2048 );
		$replyhx=strToHex($reply);
		print "FORECV: $reply\n";
		print "FORECV: $replyhx\n";
	}
		
}
while (1)
{
	$reply=socket_read ( $socket , 2048 );
	$replyhx=strToHex($reply);
	if ($reply===false or $reply=="")
		break;
	print "PORECV: $reply\n";
	print "PXRECV: $replyhx\n";
}
	
socket_close ( $socket );

?>