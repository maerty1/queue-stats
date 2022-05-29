<?php
include 'head.php';
chdir(dirname(__FILE__));
$refresh = "60";
$host = 'localhost'; // сервер БД
$user = 'root'; // имя пользователя
$password = ''; // пароль БД
$dbname = 'asteriskcdrdb'; // Имя БД
$queue = $_GET['queue'];
$time_hours = 2; // время поиска

$end = date("Y-m-d H:i:s"); // текущее время
$start = date("Y-m-d 07:00:00"); // текущее время минус время поиска

if(!isset($_SERVER['SERVER_NAME'] )) {
	$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = 'bla-bla.ru';
	}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $user, $password, $dbname) or die ('Невозможно открыть базу');

// ------ Запросы  ------- //

   $sql_in = "SELECT asteriskcdrdb.queuelog.time AS 'Время', SUBSTR( asteriskcdrdb.cdr.src, -10, 11 ) AS 'Номер звонящего', asteriskcdrdb.queuelog.queuename AS 'Номер очереди', asteriskcdrdb.queuelog.data2 AS 'Позиция входа', asteriskcdrdb.queuelog.data1 AS 'Позиция выхода', asteriskcdrdb.queuelog.event AS 'Событие', asteriskcdrdb.queuelog.data3 AS 'Время в очереди'  FROM asteriskcdrdb.queuelog INNER JOIN asteriskcdrdb.cdr ON asteriskcdrdb.queuelog.callid = asteriskcdrdb.cdr.uniqueid WHERE asteriskcdrdb.queuelog.time >= '$start' AND asteriskcdrdb.queuelog.time <= '$end' AND asteriskcdrdb.queuelog.event IN ( 'ABANDON', 'EXITWITHTIMEOUT' ) AND asteriskcdrdb.cdr.src >= 10000 AND queuelog.queuename IN ($queue) GROUP BY `Номер звонящего`;";
   $sql_in_all = "SELECT asteriskcdrdb.queuelog.time AS 'Время', SUBSTR( asteriskcdrdb.cdr.src, -10, 11 ) AS 'Номер звонящего', asteriskcdrdb.queuelog.queuename AS 'Номер очереди', asteriskcdrdb.queuelog.data2 AS 'Позиция входа', asteriskcdrdb.queuelog.data1 AS 'Позиция выхода', asteriskcdrdb.queuelog.EVENT AS 'Событие', asteriskcdrdb.queuelog.data3 AS 'Время в очереди'  FROM asteriskcdrdb.queuelog INNER JOIN asteriskcdrdb.cdr ON asteriskcdrdb.cdr.uniqueid = asteriskcdrdb.queuelog.callid WHERE asteriskcdrdb.queuelog.time >= '$start' AND asteriskcdrdb.queuelog.time <= '$end' AND asteriskcdrdb.queuelog.callid = asteriskcdrdb.cdr.uniqueid AND asteriskcdrdb.queuelog.EVENT IN ( 'COMPLETECALLER', 'COMPLETEAGENT' ) AND asteriskcdrdb.queuelog.queuename IN ( $queue ) AND asteriskcdrdb.cdr.disposition = 'ANSWERED' GROUP BY `Номер звонящего` ORDER BY 'Время' ASC;";
   $sql_out = "SELECT asteriskcdrdb.cdr.calldate AS 'Время', asteriskcdrdb.cdr.src AS 'Кто звонил', SUBSTR( asteriskcdrdb.cdr.dst, -10, 11 ) AS 'Кому звонил', asteriskcdrdb.cdr.billsec AS 'Время разговора' FROM asteriskcdrdb.cdr WHERE asteriskcdrdb.cdr.calldate >= '$start' AND asteriskcdrdb.cdr.calldate <= '$end' AND asteriskcdrdb.cdr.dst >= 10000 AND asteriskcdrdb.cdr.src <= 10000 AND asteriskcdrdb.cdr.billsec >= 9 GROUP BY `Кому звонил`;";
		$query_in = $conn->query($sql_in);
		$result_in = mysqli_query($conn, $sql_in);
		$query_in_all = $conn->query($sql_in_all);
		$result_in_all = mysqli_query($conn, $sql_in_all);
		$query_out = $conn->query($sql_out);
		$result_out = mysqli_query($conn, $sql_out);

// ----- Запросы ------ //

// ----- Создание массива ------ //
$query_in_all = $conn->query($sql_in_all);
$result_in_all = mysqli_query($conn, $sql_in_all);
$row_in_all = mysqli_fetch_array($result_in_all, MYSQLI_ASSOC);
while ($row_in_all = $query_in_all->fetch_assoc())
{
	$arr_src_all[] = $row_in_all['Номер звонящего'];
}

$query_in = $conn->query($sql_in);
$result_in = mysqli_query($conn, $sql_in);
$row_in = mysqli_fetch_array($result_in, MYSQLI_ASSOC);
while ($row_in = $query_in->fetch_assoc())
{
	$arr_src[] = $row_in['Номер звонящего'];
}
$query_out = $conn->query($sql_out);
$result_out = mysqli_query($conn, $sql_out);
$row_out = mysqli_fetch_array($result_out, MYSQLI_ASSOC);
while ($row_out = $query_out->fetch_assoc())
{
	$arr_dst[] = $row_out['Кому звонил'];
}
// ----- Создание массива ------ //

// ----- Исключаем только разность ------ //
$numbers_yes = array_diff($arr_src, $arr_dst); 
// ----- Исключаем только разность ------ //

// ----- Показываем только разность ------ //
$numbers_no_all = array_diff($arr_src, $arr_src_all); 
$numbers_no_src = array_diff($numbers_no_all, $numbers_yes);
$numbers_no = array_diff($numbers_no_all, $arr_dst);
// ----- Показываем только разность ------ //

$conn->close(); // закрытие подключения к mysql
$url=$_SERVER['REQUEST_URI'];
header("Refresh: $refresh; URL=$url");
$c_arr_src_all = count($arr_src_all);
$c_arr_src = count($arr_src);
$c_numbers_no = count($numbers_no);
?>

  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Topping');
        data.addColumn('number', 'Slices');
        data.addRows([
          ['Все принятые входящие номера', <?= $c_arr_src_all ?>],
          ['Пропущенные номера', <?= $c_arr_src ?>],
          ['нужно перезвонить', <?= $c_numbers_no ?>],
        ]);
        var options = {'title':'Процентное соотношение',
                       'width':600,
                       'height':500,
					   'pieHole': 0.3};
        var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
        chart.draw(data, options);
      }
    </script> 
  </head>

<body>

	<center><TABLE width='100%' border=0 cellpadding=0 cellspacing=0>
		<CAPTION><center><?php echo "Отчет за $start по $end очередь $queue (Обновление данных каждые $refresh секунд)" ?></center></CAPTION>
		<TBODY>
		<TR>
			<td width=20% rowspan="3"><center><div id="donutchart"></div></center></td>
			<TD width=60%><?php echo 'Все входящие номера (кто сам дозвонился)' ?>:</TD>
			<TD width=20%><?php foreach ($arr_src_all as $row) { echo $row . "<br>\r\n"; } ?></TD>
		</TR>
		</TR>
			<TD><?php echo "Пропущенные номера (кто не дозвонился)" ?>:</TD>
			<TD><?php foreach ($arr_src as $row) {	echo $row . "<br>\r\n"; } ?></TD>
		</TR>
		</TR>
		<TR>
			<TD><?php echo "Из которых нужно перезвонить" ?>:</TD>
			<TD><?php foreach ($numbers_no as $row) { echo $row . "<br>\r\n"; } ?></TD>
		</TR>
		</TBODY>
	</TABLE></center>
    <!--Div that will hold the pie chart-->
    
</body>
