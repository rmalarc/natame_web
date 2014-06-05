<?php
$username="nowtracking";
$password="";
$database="nowtracking";


$TAX_ID = $_GET['TAX_ID'];
//error_log($TID);

if (!is_numeric($TAX_ID)){
        die("TAX_ID is empty");
}


$output = shell_exec("/home/ubuntu/nowtracking/bayes2.pl $TAX_ID");

//error_log("bayes2.pl output: $output");

//header("Content-type: application/json");
header("Content-type: application/json");
header("Access-Control-Allow-Origin: *");


$json = "{ \"status\" : \"OK\"}";
echo isset($_GET['callback'])
    ? "{$_GET['callback']}($json)"
    : $json;


?>
