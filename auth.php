<?php

include_once("cf.class.php");

function msg($s){
    $_SESSION["login_msg"]=$s;
    header("Location: index.php");
    exit(0);
}

if (Enable_reCAPTCHA)
{
    if (!empty($_POST["g-recaptcha-response"]))
    {
        if (!($cloudflare->reCAPTCHA($_POST["g-recaptcha-response"])))
        {
            msg("Please complete the captcha.");
        }
    }else{
        msg("Please complete the captcha.");
    }
}

if ((!empty($_POST["email"]))&&(!empty($_POST["password"])))
{
    $r=$cloudflare->login($_POST["email"],$_POST["password"]);
    if ($r["result"]=="success")
    {
        $_SESSION["user_key"]=$r["response"]["user_key"];
        $_SESSION["email"]=$r["response"]["cloudflare_email"];
        header("Location: domains.php");
    }else{
        msg("Failed to sign in/up: ".$r["msg"]);
    }
}else{
    msg("Username and password mustn't be empty.");
}