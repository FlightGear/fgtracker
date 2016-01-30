<?
  $filename="/var/www/ahven.eu/fgtracker/modules/fgtracker/mpservers_connected.txt";

  if ($file=fopen($filename,"rt"))
  {
  	while (!feof($file))
  	{
     		$line=fgets($file);
     		echo $line;
  	}
	
  	fclose($file);
  }
?>
