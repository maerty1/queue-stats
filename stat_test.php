<?php
require_once("config.php");
include("sesvars.php");
?>
<!DOCTYPE html>

<head>
    <?php include 'head.php'; ?>
</head>
<?php
// подготовка очереди к поиску по регулярке 
$queue_reg = str_replace('\'1', '\'^1', $queue);
$queue_reg = str_replace('\'2', '\'^2', $queue_reg);
$queue_reg = str_replace('\'3', '\'^3', $queue_reg);
$queue_reg = str_replace('\'4', '\'^4', $queue_reg);
$queue_reg = str_replace('\'', '', $queue_reg);
$queue_reg = str_replace(',^', '|^', $queue_reg);
$queue_reg = str_replace('49', '', $queue_reg);
$queue_reg = str_replace('50', '', $queue_reg);
$queue_reg = str_replace('00', '', $queue_reg);
$queue_reg = str_replace('99', '', $queue_reg);
$queue_reg = str_replace('03', '', $queue_reg);
$queue_reg = str_replace('01', '', $queue_reg);
$queue_reg = str_replace('05', '', $queue_reg);
$queue_reg = str_replace('30', '', $queue_reg);
// подготовка очереди к поиску по регулярке

// Оповещение переменных
$out = array();
$in = array();
$rec_in = array();
$rec_out = array();
$total_calls = 0;
$total_abandon_calls = 0;
$total_timeout_calls = 0;
// Оповещение переменных

// sql запросы
$sql_in = "SELECT time, callid, queuename, event, data1, data2, data3 FROM $DBTable WHERE time >= '$start' AND time <= '$end' AND event IN ('ABANDON', 'EXITWITHTIMEOUT') AND queuename IN ($queue)";
$sql_out = "SELECT calldate, uniqueid, billsec, disposition, outbound_cnum, cnum, dst_cnam, recordingfile, src, dst, linkedid, recordingfile, clid, cnam from cdr where cnum REGEXP ('$queue_reg') AND calldate >= '$start' AND calldate <= '$end' AND LENGTH(outbound_cnum) >= 4 AND lastapp != 'Hangup';";
$sql_query = "SELECT time, queuename, agent, event, data1, data2, data3 FROM $DBTable WHERE time >= '$start' AND time <= '$end' AND event IN ('COMPLETECALLER', 'COMPLETEAGENT') AND queuename IN ($queue) AND agent in ($agent)";
// sql запросы

// подключение к базе
$res_out = $connection->query($sql_out);
$res_in = $connection->query($sql_in);
$res_query = $connection->query($sql_query);
// подключение к базе

while ($row = $res_out->fetch_assoc()) {
    $row['rec'] = getRec($row['recordingfile'], $row['calldate']);
    $total_out["$row[2]"]++;
    $out[] = $row;
}

foreach ($out as $k => $r) {
    $time = strtotime($r['calldate']);
    $time = date('Y-m-d H:i:s', $time);
    $min = seconds2minutes($r['billsec']);
}

// Пропущенные



while ($row = mysqli_fetch_row($res_in)) {
    $queue_calls["$row[2]"] += count($row[1]);
    $abandon["$row[2]"][] = $row[6];
    if ($row[3] == "ABANDON") {
        if ($row[6] <= 10) {
            $abandon10["$row[2]"] += count($row[6]);
        } elseif (($row[6] >= 11) && ($row[6] <= 20)) {
            $abandon20["$row[2]"] += count($row[6]);
        } elseif (($row[6] >= 21) && ($row[6] <= 30)) {
            $abandon30["$row[2]"] += count($row[6]);
        } elseif (($row[6] >= 31) && ($row[6] <= 40)) {
            $abandon40["$row[2]"] += count($row[6]);
        } elseif (($row[6] >= 41) && ($row[6] <= 50)) {
            $abandon50["$row[2]"] += count($row[6]);
        } elseif (($row[6] >= 51) && ($row[6] <= 60)) {
            $abandon60["$row[2]"] += count($row[6]);
        } elseif ($row[6] >= 61) {
            $abandon61["$row[2]"] += count($row[6]);
        }
    }
}

while ($row = $res_in->fetch_assoc()) {
    $row['rec'] = getRec($row['recordingfile'], $row['time']);
    $in[] = $row;
}

foreach ($res_in as $row) {
    $total_calls += count($row['callid']);
    $total_hold += $row['data3'];
    $abandon_end_pos += $row['data1'];
    $abandon_start_pos += $row['data2'];
    if ($row['event'] == "ABANDON") {
        $total_abandon_calls += count($row['callid']);
        $event_abandon = $lang["$language"]['user_abandon'];
    } elseif ($row['event'] == "EXITWITHTIMEOUT") {
        $total_timeout_calls += count($row['callid']);
        $event_timeout = $lang["$language"]['timeout'];
    }
    $abandon_average_hold = number_format($total_hold / $total_calls, 2);
    $abandon_average_start = round($abandon_start_pos / $total_calls);
    $abandon_average_end = floor($abandon_end_pos / $total_calls);
}

mysqli_free_result($res_in);
mysqli_free_result($res_out);
// Пропущенные

// Принятые
while ($row = mysqli_fetch_row($res_query)) {
    $total_calls2["$row[2]"]++;
    $record["$row[2]"][] = $row[0] . "|" . $row[1] . "|" . $row[3] . "|" . $row[4];
    $total_hold2["$row[2]"] += $row[4];
    $total_time2["$row[2]"] += $row[5];
    $grandtotal_hold += $row[4];
    $grandtotal_time += $row[5];
    $grandtotal_calls++;
    $hold["$row[1]"][] = $row[4];
    if ($row[4] <= 5) {
        $hold15["$row[1]"] += count($row[4]);
    } elseif (($row[4] >= 6) && ($row[4] <= 10)) {
        $hold30["$row[1]"] += count($row[4]);
    } elseif (($row[4] >= 11) && ($row[4] <= 15)) {
        $hold45["$row[1]"] += count($row[4]);
    } elseif (($row[4] >= 16) && ($row[4] <= 20)) {
        $hold60["$row[1]"] += count($row[4]);
    } elseif (($row[4] >= 21) && ($row[4] <= 25)) {
        $hold75["$row[1]"] += count($row[4]);
    } elseif (($row[4] >= 26) && ($row[4] <= 30)) {
        $hold90["$row[1]"] += count($row[4]);
    } elseif ($row[4] >= 31) {
        $hold91["$row[1]"] += count($row[4]);
    }
    $durall["$row[1]"][] = $row[5];
    if ($row[5] <= 5) {
        $dur5["$row[1]"] += count($row[5]);
    } elseif (($row[5] >= 6) && ($row[5] <= 10)) {
        $dur10["$row[1]"] += count($row[5]);
    } elseif (($row[5] >= 11) && ($row[5] <= 15)) {
        $dur15["$row[1]"] += count($row[5]);
    } elseif (($row[5] >= 16) && ($row[5] <= 20)) {
        $dur20["$row[1]"] += count($row[5]);
    } elseif (($row[5] >= 21) && ($row[5] <= 25)) {
        $dur25["$row[1]"] += count($row[5]);
    }
}


// Принятые

foreach ($res_query as $row) {
    if ($row['event'] == "COMPLETEAGENT") {
        $action_agent = $lang["$language"]['agent_hungup'];
        $num += count($row['event']);
    } elseif ($row['event'] == "COMPLETECALLER") {
        $action_caller = $lang["$language"]['caller_hungup'];
        $num2 += count($row['event']);
    }
}

// Принятые

foreach ($in as $k => $r) {
    $time = strtotime($r['time']);
    $time = date('Y-m-d H:i:s', $time);
    $dur = seconds2minutes($r['dur']);
    $wait = seconds2minutes($r['wait']);
}

// суммирование звонков
$total_out_print = array_sum($total_out);
$total_calls_print = array_sum($total_calls2);
$total_duration_print = ceil(array_sum($total_time2) / 60);
$average_duration = ceil(array_sum($total_time2) / $total_calls_print);
$average_hold = ceil(array_sum($total_hold2) / $total_calls_print);
// суммирование звонков

$in = json_encode($in);
$out = json_encode($out);
$query = json_encode($query);

$start_parts = explode(" ,:", $start);
$end_parts = explode(" ,:", $end);
mysqli_close($connection);
$connection->close();

function getRec($recfile, $time)
{
    $time = strtotime($time);
    $rec['path'] = RECPATH . date('Y/m/d/', $time) . $recfile;
    if (file_exists($rec['path']) && preg_match('/(.*)\..+$/i', $recfile)) {
        $tmpRes = base64_encode($rec['path']);
    } else {
        $tmpRes = $_REQUEST['recfile'];
    }
    return $tmpRes;
}

?>

<body>
    <?php include("menu.php"); ?>

    <script>
        let ins = <?php echo $in; ?>;
        let outs = <?php echo $out; ?>;

        function look(type) {
            param = document.getElementById(type);
            if (param.style.display == "none") param.style.display = "block";
            else param.style.display = "none"
        }

        function inOverData(arr) {
            let eve_in = {};
            let res_in = {};

            arr.map(v => [v.agent, v.event]).map(v => eve_in[v] = (eve_in[v] || 0) + 1);
            arr.map(v => [v.agent, v.disposition]).map(v => eve_in[v] = (eve_in[v] || 0) + 1);

            Object.keys(eve_in).map(v => v.split(",")).map((v, i) => {
                return [v[0], v[1], Object.values(eve_in)[i]];
            }).map(v => {
                if (v[0] in res_in) {
                    let agent_in = res_in[v[0]];
                    let event_in = {
                        [v[1]]: v[2]
                    };
                    res_in[v[0]] = {
                        ...agent_in,
                        ...event_in
                    };
                } else {
                    let agent_in = {
                        "agent": v[0]
                    };
                    let event_in = {
                        [v[1]]: v[2] || 0
                    };
                    res_in[v[0]] = {
                        ...agent_in,
                        ...event_in
                    };
                }
            });

            return res_in;
        }

        function outOverData(arr) {
            let eve_out = {};
            let res_out = {};

            arr.map(v => [v.cnum, v.event]).map(v => eve_out[v] = (eve_out[v] || 0) + 1);
            arr.map(v => [v.cnum, v.disposition]).map(v => eve_out[v] = (eve_out[v] || 0) + 1);

            Object.keys(eve_out).map(v => v.split(",")).map((v, i) => {
                return [v[0], v[1], Object.values(eve_out)[i]];
            }).map(v => {
                if (v[0] in res_out) {
                    let agent_out = res_out[v[0]];
                    let event_out = {
                        [v[1]]: v[2]
                    };
                    res_out[v[0]] = {
                        ...agent_out,
                        ...event_out
                    };
                } else {
                    let agent_out = {
                        "agent": v[0]
                    };
                    let event_out = {
                        [v[1]]: v[2] || 0
                    };
                    res_out[v[0]] = {
                        ...agent_out,
                        ...event_out
                    };
                }
            });

            return res_out;
        }

        var over_in = inOverData(ins);
        over_in = JSON.stringify(over_in);
        over_in = over_in.replace(/NO\sANSWER/g, "NO_ANSWER");
        over_in = JSON.parse(over_in);

        var over_out = outOverData(outs);
        over_out = JSON.stringify(over_out);
        over_out = over_out.replace(/NO\sANSWER/g, "NO_ANSWER");
        over_out = JSON.parse(over_out);


        $(function() {
            var theTemplateScript = $("#in-template").html();
            var theTemplate = Handlebars.compile(theTemplateScript);
            var context = {
                in: ins
            };
            var theCompiledHtml = theTemplate(context);
            $('.in-placeholder').html(theCompiledHtml);
        });

        $(function() {
            var theTemplateScript = $("#out-template").html();
            var theTemplate = Handlebars.compile(theTemplateScript);
            var context = {
                out: outs
            };
            var theCompiledHtml = theTemplate(context);
            $('.out-placeholder').html(theCompiledHtml);
        });

        $(document).ready(function() {
            if (navigator.language == 'ru')

                $('#inTable').DataTable({
                    "language": dataTablesLocale['ru'],
                    "iDisplayLength": 100
                });
            else
                $('#inTable').DataTable({
                    "iDisplayLength": 100
                });
        });

        Handlebars.registerHelper("getStatus", function(s) {
            switch (s) {
                case 'ANSWERED':
                    status = '<span style="color: green">Отвечено</span>';
                    break;
                case 'NO ANSWER':
                    status = '<span style="color: grey">Не ответили</span>';
                    break;
                case 'BUSY':
                    status = '<span style="color: firebrick">Занято</span>';
                    break;
            }
            return status;

        });

        Handlebars.registerHelper("prettyDate", function(timestamp) {
            //var a = Date.parse(timestamp);
            var a = new Date(timestamp * 1000);
            if (navigator.language == 'ru') {
                var months = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июня', 'Июля', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
            } else {
                var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            }
            var year = a.getFullYear();
            var month = months[a.getMonth()];
            var date = a.getDate();
            //var hour = a.getHours();
            var hour = (a.getHours() < 10 ? '0' : '') + a.getHours();
            //var min = a.getMinutes();
            var min = (a.getMinutes() < 10 ? '0' : '') + a.getMinutes();
            //var sec = a.getSeconds();
            var sec = (a.getSeconds() < 10 ? '0' : '') + a.getSeconds();
            if (a < 3600000)
                var time = min + ':' + sec;
            else
                var time = date + ' ' + month + ' ' + hour + ':' + min + ':' + sec;

            return time;
        });

        Handlebars.registerHelper("dataNorm", function(d) {
            if (d == undefined)
                return "0";
            else
                return d;

        });

        Handlebars.registerHelper("html5Player", function(p) {
            var player = '<audio id="player" controls preload="none"><source src="dl.php?f=' + p + '"></audio>';
            return player;
        });

        Handlebars.registerHelper("getStatus", function(s) {
            switch (s) {
                case 'COMPLETECALLER':
                    status = '<span style="color: limegreen">Абонент</span>';
                    break;
                case 'COMPLETEAGENT':
                    status = '<span style="color: royalblue">Агент</span>';
                    break;
            }
            return status;

        });

        $(document).ready(function() {
            if (navigator.language == 'ru')

                $('#outTable').DataTable({
                    "language": dataTablesLocale['ru'],
                    "iDisplayLength": 100
                });
            else
                $('#outTable').DataTable({
                    "iDisplayLength": 100
                });
        });

        Handlebars.registerHelper("getStatus", function(s) {
            switch (s) {
                case 'ANSWERED':
                    status = '<span style="color: green">Отвечено</span>';
                    break;
                case 'NO ANSWER':
                    status = '<span style="color: grey">Не ответили</span>';
                    break;
                case 'BUSY':
                    status = '<span style="color: firebrick">Занято</span>';
                    break;
            }
            return status;

        });

        Handlebars.registerHelper("html5Player", function(p, d) {

            if (d == "ANSWERED")
                var player = '<audio id="player" controls preload="none"><source src="dl.php?f=' + p + '"></audio>';
            else
                var player = '<p style="font-size: 16pt">&#x274e;</p>';
            return player;
        });
    </script>

    <script id="in-template" type="text/x-handlebars-template">
        <div class="table table-list-search">
		<table id="inTable" class="table table-striped">
			<thead>
				<tr>
					<th>Дата</th>
					<th>Вход. номер</th>
                    <th>Вн. номер</th>
                    <th>Очередь</th>
                    <th>Агент</th>
                    <th>Ожид.</th>
                    <th>Разг.</th>
                    <th>Заверш.</th>
                    <th>Запись</th>
				</tr>
			</thead>
			<tbody>
                {{#each in}}
				<tr>
					<td>{{prettyDate callid}}</td>
                    <td>{{src}}</td>
                    <td>{{did}}</td>
                    <td>{{queuename}}</td>
                    <td>{{agent}}</td>
                    <td>{{prettyDate wait}}</td>
                    <td>{{prettyDate dur}}</td>
                    <td>{{{getStatus event}}}</td>
                    <td>{{{html5Player rec}}}</td>

                </tr>
                {{/each}}
                </tbody>
            </table>
</script>

    <script id="out-template" type="text/x-handlebars-template">
        <div class="table table-list-search">
        <table id="outTable" class="table table-striped">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Агент</th>
                    <th>Внутренний</th>
                    <th>Набранный</th>
                    <th>Продолжительность</th>
                    <th>Статус</th>
                    <th>Запись</th>
                </tr>
            </thead>
            <tbody>
                {{#each out}}
                <tr>
                    <td>{{prettyDate uniqueid}}</td>
                    <td>{{cnam}} ({{cnum}})</td>
                    <td>{{src}}</td>
                    <td>{{dst}}</td>
                    <td>{{prettyDate billsec}}</td>
                    <td>{{{getStatus disposition}}}</td>
                    <td>{{{html5Player rec disposition}}}</td>
                </tr>
                {{/each}}
            </tbody>
        </table>
</script>

    <div id="main">
        <div id="contents">
            <TABLE width='99%' cellpadding=2 cellspacing=1 border=0>
                <THEAD>
                    <TR>
                        <TD valign=top width='50%'>
                            <TABLE width='100%' border=0 cellpadding=0 cellspacing=0>
                                <CAPTION><?php echo $lang["$language"]['report_info'] ?></CAPTION>
                                <TBODY>
                                    <TR>
                                        <TD><?php echo $lang["$language"]['queue'] ?>:</TD>
                                        <TD><?php echo $queue ?></TD>
                                    </TR>
                                    <TR>
                                        <TD><?php echo $lang["$language"]['start'] ?>:</TD>
                                        <TD><?php echo $start_parts[0] ?></TD>
                                    </TR>
                                    <TR>
                                        <TD><?php echo $lang["$language"]['end'] ?>:</TD>
                                        <TD><?php echo $end_parts[0] ?></TD>
                                    </TR>
                                    <TR>
                                        <TD><?php echo $lang["$language"]['period'] ?>:</TD>
                                        <TD><?php echo $period ?><?php echo  " " . $lang["$language"]['days'] ?></TD>
                                    </TR>
                                </TBODY>
                            </TABLE>
                        </TD>
                        <TD valign=top width='50%'>
                            <TABLE width='100%' border=0 cellpadding=0 cellspacing=0>
                                <CAPTION><?php echo $lang["$language"]['all_calls'] ?></CAPTION>
                                <TBODY>
                                    <TR>
                                        <TD><?php echo $lang["$language"]['unanswered'] ?>:</TD>
                                        <TD><?php echo $total_calls ?><?php echo " " . $lang["$language"]['calls'] ?></TD>
                                    </TR>
                                    <TR>
                                        <TD><?php echo $lang["$language"]['outbound'] ?>:</TD>
                                        <TD><?php echo $total_out_print ?><?php echo " " . $lang["$language"]['calls'] ?></TD>
                                    </TR>
                                    <TR>
                                        <TD><?php echo $lang['ru']['answered'] ?>:</TD>
                                        <TD><?php echo $total_calls_print  ?><?php echo " " . $lang["$language"]['calls'] ?></TD>
                                    </TR>
                                    <TR>
                                        <TD><?php echo $lang['ru']['answered'] . ", " . $lang["$language"]['caller_hungup'] ?>:</TD>
                                        <TD><?php echo $num ?><?php echo " " . $lang["$language"]['calls'] ?></TD>
                                    </TR>
                                    <TR>
                                        <TD><?php echo $lang['ru']['answered'] . ", " . $lang["$language"]['agent_hungup'] ?>:</TD>
                                        <TD><?php echo $num2 ?><?php echo " " . $lang["$language"]['calls'] ?></TD>
                                    </TR>
                                </TBODY>
                            </TABLE>
                        </TD>
                    </TR>
                </THEAD>
            </TABLE>
            <div id="contents">
                <h2>Принятые вызовы <button onClick="javascript:look('div_in');" style="float: right;">Подробнее</button></h2>
                <div class="in-placeholder" id="div_in" style="display:none"></div>
                <h2>Исходящие вызовы <button onClick="javascript:look('div_out');" style="float: right;">Подробнее</button></h2>
                <div class="out-placeholder" id="div_out" style="display:none"></div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>

</html>
