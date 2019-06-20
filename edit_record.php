<?php
include_once("cf.class.php");

$cloudflare->is_login();

function msg($s){
    $_SESSION["mng_msg"]=$s;
    header("Location: manage_domain.php?domain=".$_POST["domain"]);
    exit(0);
}

if (empty($_POST["domain"])){
    $_SESSION["msg"]="Domain name mustn't be empty.";
    header("Location: domains.php");
    exit(0);
}

if (empty($_POST["action"])){
    msg("Unknown action.");
}

if (empty($_POST["record"])){
    msg("Record mustn't be empty.");
}

$re=$cloudflare->zone_lookup($_POST["domain"]);
if ($re["result"]!="success"){
    msg("Error: ".$re["msg"]);
}
if ($re["response"]["zone_exists"]!=true){
    msg("This domain isn't managed by Cloudflare.");
}
if ($re["response"]["zone_hosted"]!=true){
    msg("This domain isn't managed by ".SITE_NAME.".");
}

$r=$cloudflare->remove_zone_name($re["response"]["zone_name"],$re["response"]);

if ($_POST["action"]=="delete")
{
    if (!empty($r["hosted_cnames"][$_POST["record"]]))
    {
        unset($r["hosted_cnames"][$_POST["record"]]);
        $result=$cloudflare->update_record($r["zone_name"],$r["hosted_cnames"]);
        if ($result["result"]=="success")
        {
            msg("Deleted successfully.");
        }else{
            msg("Failed to delete: ".$result["msg"]);
        }
    }else{
        msg("This record isn't existed.");
    }
}elseif($_POST["action"]=="edit")
{
    if (!empty($_POST["value"]))
    {
        $r["hosted_cnames"][$_POST["record"]]=$_POST["value"];
        $result=$cloudflare->update_record($r["zone_name"],$r["hosted_cnames"]);
        if ($result["result"]=="success")
        {
            msg("Updated successfully.");
        }else{
            msg("Failed to update: ".$result["msg"]);
        }
    }else{
        msg("Missing parameters.");
    }
}
