<?php
require_once("config.php");
require_once("sesvars.php");

session_start();
if (isset($_POST['agent_sync'])) {
    $aquery = "truncate agents_new;";
    $aquery .= "insert ignore into agents_new (agent) SELECT DISTINCT(agent) FROM $DBTable where agent != 'NONE'";
    $ares = mysqli_multi_query($connection, $aquery);
    $_POST['agent_sync'] = NULL;
}

if (isset($_POST['queue_sync'])) {
    $qquery = "truncate queues_new;";
    $qquery .= "insert ignore into queues_new (queuename) SELECT DISTINCT(queuename) FROM $DBTable where queuename != 'NONE'";
    $qres = mysqli_multi_query($connection, $qquery);
    $_POST['queue_sync'] = NULL;
}
session_unset();

?>