<?php
  include "../../mainfile.php";

  include "include/trig.php";
  include "include/general.php";
  include "include/mydijkstra.php";
  include "include/flightplan.php";
  include_once 'include/db_connect.php';


  $funct=get_request("FUNCT");

  if ($funct=="") $funct="INPUT";

  switch ($funct)
  {
    case "INPUT":
  		$xoopsOption['template_main'] = "fp_direct_input.html";
  		include XOOPS_ROOT_PATH."/header.php";

		$res=pg_query($conn,"select icao,name from airports order by name;");
		$nr=pg_num_rows($res);
		for ($i=0;$i<$nr;$i++)
		{
			$icao=pg_result($res,$i,'icao');
			$name=pg_result($res,$i,'name');
			$x[icao]=$icao;
			$x[name]=$name;
			$xoopsTpl->append('airports', $x);
		}
		pg_free_result($res);
    		break;
    case "PLAN":
		$departure=strtoupper(get_request("DEP"));
		$arrival=strtoupper(get_request("ARR"));
		$deprwy=get_request("DEPRWY");
		$arrrwy=get_request("ARRRWY");
		$mode=get_request("MODE");
		$vor=get_request("VOR");
		$ndb=get_request("NDB");
		$fix=get_request("FIX");
		$wptmin=get_request("WPTMIN");
		$wptmax=get_request("WPTMAX");
		if ($departure=="") $departure="LHBP";
		if ($arrival=="") $arrival="LHBP";
		if ( $wptmin=="" ) $wptmin=40;
		if ( $wptmax=="" ) $wptmax=100;
		if ($deprwy=="")
		{
			$res=pg_query($conn,"SELECT runway FROM runways WHERE airport_id=(SELECT id FROM airports WHERE icao='$departure')");
			$n=pg_num_rows($res);
			if ($n>0) $deprwy=pg_result($res,0,0);
			pg_free_result($res);
		}
		if ($arrrwy=="")
		{
			$res=pg_query($conn,"SELECT runway FROM runways WHERE airport_id=(SELECT id FROM airports WHERE icao='$arrival')");
			$n=pg_num_rows($res);
			if ($n>0) $arrrwy=pg_result($res,0,0);
			pg_free_result($res);
		}
  		$xoopsOption['template_main'] = "fp_direct_show.html";
  		include XOOPS_ROOT_PATH."/header.php";
		switch ($mode)
		{
		  case "DIRECT":
			direct_plan($conn,$departure,$deprwy,$arrival,$arrrwy);
			break;
		  case "NAVAID":
			navaid_plan($conn,$departure,$deprwy,$arrival,$arrrwy,$vor,$ndb,$fix,$wptmin,$wptmax);
			break;
		}
		break;
  }



  pg_close($conn);

  include_once XOOPS_ROOT_PATH."/footer.php";
?>
