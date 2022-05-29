<?php
include 'head.php';
$url="php_call.php";

$host = 'localhost'; // сервер БД
$user = 'root'; // имя пользователя
$password = ''; // пароль БД
$dbname = 'asteriskcdrdb'; // Имя БД

if(!isset($_SERVER['SERVER_NAME'] )) {
	$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = 'bla-bla.ru';
	}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $user, $password, $dbname) or die ('Невозможно открыть базу');
mysqli_set_charset($conn, "utf8");  
$sql_queue = "SELECT asterisk.queues_config.extension AS 'Номер', asterisk.queues_config.descr AS 'Название' FROM asterisk.queues_config WHERE asterisk.queues_config.descr LIKE '%ОП' OR asterisk.queues_config.descr LIKE '%Рец%';";
$query_queue = $conn->query($sql_queue);
$result_queue = mysqli_query($conn, $sql_queue);
$row_queue = mysqli_fetch_array($result_queue, MYSQLI_ASSOC);
?>

<style type="text/css" media="screen">@import "css/button.css";</style>
<html>
<body>
<center><TABLE width='100%' border=0 cellpadding=0 cellspacing=0><TBODY>
<?php
		$x = "1";
while ($row_queue = $query_queue->fetch_assoc())
{
		if ($x < "2") {
			echo "<TR>";
			echo "<TD height=1%><form action=\"$url\" method='GET'><input type='hidden' name='queue' type='submit' value='$row_queue[Номер]'/><input class='shine-button' type='submit'  value='$row_queue[Название]' /></form><br><br></TD>";
			$x = $x + 1;
		} else {
			echo "<TD height=1%><form action=\"$url\" method='GET'><input type='hidden' name='queue' type='submit' value='$row_queue[Номер]'/><input class='shine-button' type='submit'  value='$row_queue[Название]' /></form><br><br></TD>";
			echo "</TR>";
			$x = "1";
		}
}
?>
</TBODY></TABLE></center>
 </body>
</html>
