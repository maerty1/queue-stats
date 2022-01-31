<?php
chdir(dirname(__FILE__));
$host = 'localhost'; // сервер БД
$user = 'root'; // имя пользователя
$password = ''; // пароль БД
$dbname = 'asteriskcdrdb'; // Имя БД

$time_hours = 24; // время поиска
$end = date("Y-m-d H:i:s"); // текущее время
$start = date("Y-m-d H:i:s", strtotime("-$time_hours hours", strtotime($end))); // текущее время минус время поиска

if(!isset($_SERVER['SERVER_NAME'] )) {
	$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = 'localhost';
	}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $user, $password, $dbname) or die ('Невозможно открыть базу');

// ------ Запрос входящих ------- //

   $sql_in = "SELECT
	asteriskcdrdb.queuelog.time AS 'Время',
	SUBSTR( asteriskcdrdb.cdr.src, - 10, 10 ) AS 'Номер звонящего',
	asteriskcdrdb.queuelog.queuename AS 'Номер очереди',
	asteriskcdrdb.queuelog.data2 AS 'Позиция входа',
	asteriskcdrdb.queuelog.data1 AS 'Позиция выхода',
	asteriskcdrdb.queuelog.event AS 'Событие',
	asteriskcdrdb.queuelog.data3 AS 'Время в очереди' 
FROM
	asteriskcdrdb.queuelog
	INNER JOIN asteriskcdrdb.cdr ON asteriskcdrdb.queuelog.callid = asteriskcdrdb.cdr.uniqueid 
WHERE
	asteriskcdrdb.queuelog.time >= '$start' 
	AND asteriskcdrdb.queuelog.time <= '$end' AND asteriskcdrdb.queuelog.event IN ( 'ABANDON', 'EXITWITHTIMEOUT' ) AND asteriskcdrdb.cdr.src >= 10000
GROUP BY
	`Номер звонящего`;";
	   $query_in = $conn->query($sql_in);
	   $result_in = mysqli_query($conn, $sql_in);
   echo "Отчет за последние $time_hours ч. от $end";

// ----- Запрос входящих ------ //



// ----- Запрос исходящих ------ //
   $sql_out = "SELECT
	asteriskcdrdb.cdr.calldate AS 'Время',
	asteriskcdrdb.cdr.src AS 'Кто звонил',
	SUBSTR( asteriskcdrdb.cdr.dst, - 10, 10 ) AS 'Кому звонил',
	asteriskcdrdb.cdr.billsec AS 'Время разговора' 
FROM
	asteriskcdrdb.cdr 
WHERE
	asteriskcdrdb.cdr.calldate >= '$start' 
	AND asteriskcdrdb.cdr.calldate <= '$end' 
	AND asteriskcdrdb.cdr.dst >= 10000 
	AND asteriskcdrdb.cdr.src <= 10000 AND asteriskcdrdb.cdr.billsec >= 9 
GROUP BY
	`Кому звонил`;";
	$query_out = $conn->query($sql_out);
	$result_out = mysqli_query($conn, $sql_out);

// ----- Запрос исходящих ------ //

// ----- Запрос входящих  создание массива ------ //
$query_in = $conn->query($sql_in);
$result_in = mysqli_query($conn, $sql_in);
$row_in = mysqli_fetch_array($result_in, MYSQLI_ASSOC);
while ($row_in = $query_in->fetch_assoc())
{
//	var_dump($row_in['Номер звонящего']);
//	echo "<br>";
	$arr_src[] = $row_in['Номер звонящего'];
}
// ----- Запрос входящих  создание массива ------ //

// ----- Запрос исходящих создание массива ------ //
$query_out = $conn->query($sql_out);
$result_out = mysqli_query($conn, $sql_out);
$row_out = mysqli_fetch_array($result_out, MYSQLI_ASSOC);
while ($row_out = $query_out->fetch_assoc())
{
	$arr_dst[] = $row_out['Кому звонил'];
}
// ----- Запрос исходящих создание массива ------ //

echo "\r\n<br>"; echo "Пропущенные номера"; echo "\r\n<br>";

// ----- Исключаем только разность ------ //
$numbers_yes = array_diff($arr_src, $arr_dst); 
foreach ($numbers_yes as $row) {
	echo $row . "<br>\r\n";
}

// ----- Исключаем только разность ------ //

echo "\r\n<br>"; echo "Перезвонили номерам"; echo "\r\n<br>";

// ----- Показываем только разность ------ //
$numbers_no = array_intersect($arr_src, $arr_dst); 

foreach ($numbers_no as $row) {
	echo $row . "<br>\r\n";
}

// ----- Показываем только разность ------ //

$numbers_yes = implode('<br>', $numbers_yes);
$numbers_no = implode('<br>', $numbers_no);

// ----- Отправка письма на почту ---- //
	// пример использования SendMailSmtpClass.php
	$today = 'Отчет за последние ' . $time_hours . ' ч. от ' . $end;
	require_once "SendMailSmtpClass.php"; // подключаем класс

	// примеры подключения
	$mailSMTP = new \SmtpMail\SendMailSmtpClass('mail1@mail.ru', 'pass', 'ssl://smtp.yandex.ru', 465, "UTF-8");
	// от кого
	$from = array(
		"Отчеты по звонкам", // Имя отправителя
		"mail1@mail.ru" // почта отправителя
	);
	// кому отправка. Можно указывать несколько получателей через запятую
	$to = 'mail2@mail.ru';

if (empty($numbers_yes)) {
			$result =  $mailSMTP->send($to, $today, 'Пропущенные звонки отсутствуют', $from);
		}else {
			$result =  $mailSMTP->send($to, $today, '<br>Пропущенные звонки от номеров:<br>' . $numbers_yes , $from);
		}

	if($result === true){
		echo "Готово";
	}else{
		echo "Ошибка: " . $result;
	}
// ------- Отправка письма на почту ------- //

$conn->close(); // закрытие подключения к mysql
?>
