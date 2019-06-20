<?php
include_once("cf.class.php");

$cloudflare->is_login();

function msg($s){
    $_SESSION["msg"]=$s;
    header("Location: domains.php");
    exit(0);
}

if (empty($_POST["domain"])){
    msg("Domain name mustn't be empty.");
}

$r=$cloudflare->zone_set($_POST["domain"],$_POST["domain"],"www:".$_POST["domain"]);

if ($r["result"]=="success"){
    msg("Success");
}else{
    msg("Error: ".$r["msg"]);
}