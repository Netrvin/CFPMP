<?php

include_once("config.php");

if (Enable_TXT_Verification&&(strlen(Random_String)<64)){
    die("Please set Random_String in config.php or disable TXT record verification");
}

session_start();

class CF
{
    public function post($data)
    {
        $data["host_key"] = HOST_KEY;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/host-gw.html");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $r = curl_exec($ch);
        curl_close($ch);
        return json_decode($r, true);
    }

    public function user_api_get($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Auth-Email: ' . $_SESSION['email'],
            'X-Auth-Key: ' . $_SESSION['api_key'],
        ));
        $r = curl_exec($ch);
        curl_close($ch);
        return json_decode($r, true);
    }

    public function user_api_post($url, $data, $method = 'POST')
    {
        $ch = curl_init();
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Auth-Email: ' . $_SESSION['email'],
            'X-Auth-Key: ' . $_SESSION['api_key'],
        ));
        $r = curl_exec($ch);
        curl_close($ch);
        return json_decode($r, true);
    }

    public function login($email, $password)
    {
        $data["act"] = (Allow_Register ? "user_create" : "user_auth");
        $data["cloudflare_email"] = $email;
        $data["cloudflare_pass"] = $password;
        $data["unique_id"] = NULL;
        return self::post($data);
    }

    public function logout()
    {
        if (!empty($_SESSION["email"])) {
            unset($_SESSION["email"]);
        }
        if (!empty($_SESSION["user_key"])) {
            unset($_SESSION["user_key"]);
        }
    }

    public function is_login()
    {
        if ((empty($_SESSION["email"])) || (empty($_SESSION["user_key"]))) {
            header("Location: index.php");
            exit(0);
        }
    }

    public function user_lookup()
    {
        $data["act"] = "user_lookup";
        $data["cloudflare_email"] = $_SESSION["email"];
        return self::post($data);
    }

    public function zone_set($zone_name, $resolve_to, $subdomains)
    {
        $data["act"] = "zone_set";
        $data["user_key"] = $_SESSION["user_key"];
        $data["zone_name"] = $zone_name;
        $data["resolve_to"] = $resolve_to;
        $data["subdomains"] = $subdomains;
        return self::post($data);
    }

    public function zone_delete($zone_name)
    {
        $data["act"] = "zone_delete";
        $data["user_key"] = $_SESSION["user_key"];
        $data["zone_name"] = $zone_name;
        return self::post($data);
    }

    public function zone_lookup($zone_name)
    {
        $data["act"] = "zone_lookup";
        $data["user_key"] = $_SESSION["user_key"];
        $data["zone_name"] = $zone_name;
        return self::post($data);
    }

    public function remove_zone_name($zone_name, $data)
    {
        foreach ($data["hosted_cnames"] as $record => $set) {
            if (strlen($record) > strlen($zone_name)) {
                $record2 = substr($record, 0, strlen($record) - strlen($zone_name) - 1);
            } else {
                $record2 = "@";
            }
            $data["hosted_cnames"][$record2] = $set;
            unset($data["hosted_cnames"][$record]);
        }
        foreach ($data["forward_tos"] as $record => $set) {
            if (strlen($record) > strlen($zone_name)) {
                $record2 = substr($record, 0, strlen($record) - strlen($zone_name) - 1);
            } else {
                $record2 = "@";
            }
            $data["forward_tos"][$record2] = $set;
            unset($data["forward_tos"][$record]);
        }
        return $data;
    }

    public function get_zone_id($zone_name)
    {
        return self::user_api_get("https://api.cloudflare.com/client/v4/zones?name=$zone_name")['result'][0]['id'];
    }

    public function get_proxied_records($zone_name){
        $zone_id = self::get_zone_id($zone_name);
        return self::user_api_get("https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records?type=CNAME&proxied=true&page=1&per_page=100&order=name&direction=asc&match=all");
    }

    public function add_record($zone_id, $name, $content){
        return self::user_api_post("https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records", [
            "type" => "CNAME",
            "name" => $name,
            "content" => self::add_suffix_for_ip($content),
            "ttl" => 1,
            "proxied" => true
        ]);
    }

    public function edit_record($zone_id, $record_id, $name, $content){
        $data = [
            'type' => 'CNAME',
            'name' => $name,
            'content' => self::add_suffix_for_ip($content),
            'ttl' => 1,
            "proxied" => true
        ];
        return self::user_api_post("https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records/$record_id", $data, "PUT");
    }

    public function delete_record($zone_id, $record_id){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records/$record_id");
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Auth-Email: ' . $_SESSION['email'],
            'X-Auth-Key: ' . $_SESSION['api_key'],
        ));
        $r = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($r, true);
        if ($data['result']['id']==$record_id) $data['success'] = true;
        else $data['success'] = false;
        return $data;
    }

    public function add_suffix_for_ip($content){
        if (Enable_A_Record&&(filter_var($content, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))){
            return $content.".sslip.io";
        }else{
            return $content;
        }
    }

    public function reCAPTCHA($response)
    {
        $url = "https://www.recaptcha.net/recaptcha/api/siteverify";
        $data = array(
            "secret" => reCAPTCHA_Secret,
            "response" => $response
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $r = curl_exec($ch);
        curl_close($ch);
        $re = json_decode($r, true);
        if (!empty($re["success"])) {
            if ($re["success"] == "true") {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function check_txt_record($domain){
        foreach(dns_get_record("cfpmp.".$domain, DNS_TXT) as $v){
            if (password_verify(Random_String.$_SESSION["email"], $v["txt"])) return true;
        }
        return false;
    }
}

$cloudflare = new CF();