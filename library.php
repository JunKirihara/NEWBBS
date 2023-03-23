<?php
/*DB接続*/
function dbconnect(){
    $db = new mysqli('mysql1.php.xdomain.ne.jp', 'junkirihara_new', 'dekirudekiru', 'junkirihara_newbbs');
    if (!$db) {
        die($db->error);
    }
    return $db;
}
/*htmlspecialchars関数化*/
function h($value){
    return htmlspecialchars($value, ENT_QUOTES);
}
?>