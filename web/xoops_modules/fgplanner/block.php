<?

  function get_request($name)
  {
     return array_key_exists($name,$_REQUEST)?$_REQUEST[$name]:"";
  }


    include_once '/include/db_connect.php';


    echo "<CENTER>";
    echo "<TABLE>";
    echo "<TR><TH CLASS=flights>Callsign</TH><TH  CLASS=flights>Total flight time</TH></TR>";

    $res=pg_query($conn,"SELECT f.callsign AS callsign,sum(f.end_time-f.start_time) AS flighttime FROM flights as f GROUP BY f.callsign HAVING sum(f.end_time-f.start_time)>0 ORDER BY sum(f.end_time-f.start_time) desc");
    $nr=pg_num_rows($res);

    for($i=0;$i<$nr;$i++)
    {
      $callsign=pg_result($res,$i,"callsign");
      $flighttime=pg_result($res,$i,"flighttime");

      echo "<TR><TD CLASS=flights><A HREF=\"/modules/fgtracker/index.php?FUNCT=FLIGHTS&CALLSIGN=$callsign\">$callsign</a></TD><TD CLASS=flights ALIGN=right>$flighttime</TD></TR>";
    }
    pg_free_result($res);
    echo "</TABLE></CENTER>";
    

?>
