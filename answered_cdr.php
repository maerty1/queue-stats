<?php
/*
Copyright 2019, https://asterisk-pbx.ru

This file is part of Asterisk Call Center Stats.
Asterisk Call Center Stats is free software: you can redistribute it
and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

Asterisk Call Center Stats is distributed in the hope that it will be
useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Asterisk Call Center Stats.  If not, see
<http://www.gnu.org/licenses/>.
 */
require_once "config.php";
include "sesvars.php";
//ini_set('display_errors',1);
//error_reporting(E_WARNING);
?>
<?php
//query mixed from queuelog and cdr (queuelog table must be in cdr databases)
$sql = "select queuelog.time, queuelog.callid, queuelog.queuename, queuelog.agent,  queuelog.event, queuelog.data1 as wait, queuelog.data2 as dur, cdr.did, cdr.src, cdr.recordingfile, cdr.disposition from queuelog, cdr where queuelog.time >= '$start' AND queuelog.time <= '$end' AND queuelog.callid = cdr.uniqueid and queuelog.event in ('COMPLETECALLER', 'COMPLETEAGENT') and queuelog.agent in ($agent) and queuelog.queuename in ($queue) and cdr.disposition = 'ANSWERED' order by queuelog.time";

$res = $connection->query($sql);

$out = array();
$rec = array();
while ($row = $res->fetch_assoc()) {
    $row['rec'] = getRec($row['recordingfile'], $row['time']);
    $out[] = $row;

}

$header_pdf = array("Дата", "Вход. номер", "Звонили", "Очередь", "Агент", "Ожид.", "Разг.");
$width_pdf = array(40, 32, 25, 25, 64, 25, 25);
$title_pdf = "Принятые вызовы";
$data_pdf = array();
foreach ($out as $k => $r) {
    $time = strtotime($r['time']);
    $time = date('Y-m-d H:i:s', $time);
    $dur = seconds2minutes($r['dur']);
    $wait = seconds2minutes($r['wait']);
    $linea_pdf = array($time, $r['src'], $r['did'], $r['queuename'], $r['agent'], $wait, $dur);
    $data_pdf[] = $linea_pdf;
}

$out = json_encode($out);

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
<!DOCTYPE html>
<head>

    <?php include 'head.php'; ?>

    <script>
        let outs = <?php echo $out; ?>;

        function outOverData(arr) {
            let eve = {};
            let res = {};

            arr.map(v => [v.agent, v.event]).map(v => eve[v] = (eve[v] || 0) + 1);
            arr.map(v => [v.agent, v.disposition]).map(v => eve[v] = (eve[v] || 0) + 1);

            Object.keys(eve).map(v => v.split(",")).map((v, i) => {
                return [v[0], v[1], Object.values(eve)[i]];
            }).map(v => {
                if (v[0] in res) {
                    let agent = res[v[0]];
                    let event = {
                        [v[1]]: v[2]
                    };
                    res[v[0]] = {...agent, ...event};
                } else {
                    let agent = {"agent": v[0]};
                    let event = {
                        [v[1]]: v[2] || 0
                    };
                    res[v[0]] = {...agent, ...event};
                }
            });

            return res;
        }

        var over_out = outOverData(outs);
        over_out = JSON.stringify(over_out);
        over_out = over_out.replace(/NO\sANSWER/g, "NO_ANSWER");
        over_out = JSON.parse(over_out);

        $(function () {
            var theTemplateScript = $("#overs-template").html();
            var theTemplate = Handlebars.compile(theTemplateScript);
            var context = {over: over_out};
            var theCompiledHtml = theTemplate(context);
            $(".overs-placeholder").html(theCompiledHtml);
        });

        $(function () {
            var theTemplateScript = $("#out-template").html();
            var theTemplate = Handlebars.compile(theTemplateScript);
            var context = {out: outs};
            var theCompiledHtml = theTemplate(context);
            $('.out-placeholder').html(theCompiledHtml);
        });

        $(document).ready(function () {
            if (navigator.language == 'ru')

                $('#incTable').DataTable(
                    {
                        "language": dataTablesLocale['ru'],
                        "iDisplayLength": 100
                    }
                );
            else
                $('#incTable').DataTable({"iDisplayLength": 100});
        });


        Handlebars.registerHelper("prettyDate", function (timestamp) {
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

        Handlebars.registerHelper("dataNorm", function (d) {
            if (d == undefined)
                return "0";
            else
                return d;

        });


        Handlebars.registerHelper("html5Player", function (p) {
            var player = '<audio id="player" controls preload="none"><source src="dl.php?f=' + p + '"></audio>';
            return player;
        });

        Handlebars.registerHelper("getStatus", function (s) {
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

    </script>

    <script id="overs-template" type="text/x-handlebars-template">
        <h2>Обзор</h2>
        <div class="table">
            <table class="table centered">
                <thead>
                <tr class="text-center">
                    <th class="text-left">Агент</th>
                    <th>Отв.</th>
                </tr>
                </thead>
                <tbody>
                {{#each over}}
                <tr class="text-center">
                    <td class="text-left">{{@key}}</td>
                    <td>{{dataNorm this.ANSWERED}}</td>
                </tr>
                {{/each}}
                </tbody>
            </table>
        </div>
    </script>
    <script id="out-template" type="text/x-handlebars-template">
        <div class="table table-list-search">
            <table id="incTable" class="table table-striped">
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
                {{#each out}}
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
</head>
<html>
<body>
<?php echo "<div style='align-content: center'>"; include 'menu.php'; echo "</div>" ?>

<div id="main">
    <div id="contents">
        <h1>Принятые вызовы: <?php echo $start . " - " . $end ?></h1>
        <br/>
        <div class="overs-placeholder"></div>
        <br/>
        <h2>Детализация</h2>
        <br/>
        <?php
        print_exports($header_pdf, $data_pdf, $width_pdf, $title_pdf, $cover_pdf);
        ?>
        <br/>
        <hr/>
        <div class="out-placeholder"></div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
