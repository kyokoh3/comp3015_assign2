<?php

function mysqlconnection()
{

	$link = mysqli_connect('localhost', 'root', 'root', 'localhost');

	if (!$link) {
		echo mysqli_connect_error();
	}
	else
	{
		echo "success<br />";

		$result = mysqli_query($link, 'SELECT * FROM posts');

		
		while ($row = mysqli_fetch_array($result)) 
		{
			echo $row[0] . '<br />';
			echo $row[1] . '<br />';
			echo $row[2] . '<br />';
			echo $row[3] . '<br />';
			echo $row[4] . '<br />';
			echo $row[5] . '<br />';
			echo $row[6] . '<br />';
			echo $row[7] . '<br />';


			mysqli_close($link);
		}
	} // if (!$link) {

} // mysqlconnection()






?>