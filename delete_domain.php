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

$r=$cloudflare->zone_delete($_POST["domain"]);

if ($r["result"]=="success"){
    msg("Deleted successfully.");
}else{
    msg("Failed to delete: ".$r["msg"]);
}