<?php
function close($db = null)
{
	$result = mysql_query("SHOW FULL PROCESSLIST");
	while ($row=mysql_fetch_array($result)) {
	$process_id=$row["Id"];
	if ($row["Time"] > 100 ) {
	$sql="KILL $process_id";
	mysql_query($sql);
	}
	}
}