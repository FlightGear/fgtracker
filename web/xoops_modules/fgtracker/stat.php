<TABLE WIDTH=100% BORDER=0>

<TR><TH COLSPAN=2># of flight coordinates recorded</TH></TR>
<TR><TH CLASS=flights>Month</TH><TH CLASS=flights>Count</TH></TR>

<?
  include('include/db_connect.php');

  $res=pg_query($conn,"select month,count from tracker_stats order by month desc limit 12;");

  $nr=pg_num_rows($res);

  for ($i=$nr-1;$i>=0;$i--)
  {
	$month=pg_result($res,$i,0);
	$count=pg_result($res,$i,1);

	printf("<TR><TD CLASS=flights>%s</TD><TD ALIGN=RIGHT CLASS=flights>%s</TD></TR>\n",$month,$count);
  }

  pg_free_result($res);
?>

</TABLE>
