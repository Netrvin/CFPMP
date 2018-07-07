<?php

include_once("config.php");

session_start();

class CF {
    public function post($data){
        $data["host_key"]=HOST_KEY;
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,"https://api.cloudflare.com/host-gw.html");
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_TIMEOUT,10);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $r=curl_exec($ch);
        curl_close($ch);
        return json_decode($r,true);
    }

    public function login($email,$password){
        $data["act"]="user_create";
		$data["cloudflare_email"]=$email;
        $data["cloudflare_pass"]=$password;
        $data["unique_id"]=NULL;
		return self::post($data);
    }

    public function logout(){
        if (!empty($_SESSION["email"])){
            unset($_SESSION["email"]);
        }
        if (!empty($_SESSION["user_key"])){
            unset($_SESSION["user_key"]);
        }
    }

    public function is_login(){
        if ((empty($_SESSION["email"]))||(empty($_SESSION["user_key"]))){
            header("Location: index.php");
            exit(0);
        }
    }

	public function user_lookup(){
		$data["act"]="user_lookup";
		$data["cloudflare_email"]=$_SESSION["email"];
		return self::post($data);
	}

    public function zone_set($zone_name,$resolve_to,$subdomains){
		$data["act"] = "zone_set";
		$data["user_key"] = $_SESSION["user_key"];
		$data["zone_name"] = $zone_name;
		$data["resolve_to"] = $resolve_to;
		$data["subdomains"] = $subdomains;
		return self::post($data);
	}

    public function zone_delete($zone_name){
	    $data["act"] = "zone_delete";
	    $data["user_key"] = $_SESSION["user_key"];
	    $data["zone_name"] = $zone_name;
	    return self::post($data);
	}

    public function zone_lookup($zone_name){
		$data["act"] = "zone_lookup";
		$data["user_key"] = $_SESSION["user_key"];
		$data["zone_name"] = $zone_name;
	    return self::post($data);
	}

    public function update_record($zone_name,$record){
        if (empty($record["@"])){
            $record["@"]=$zone_name;
        }
        $at=$record["@"];
        unset($record["@"]);
        if ((Enable_A_Record) && (filter_var($at,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4))){
            $at=$at.'.xip.io';
        }
        $str="";
        foreach ($record as $key => $value){
            if ((Enable_A_Record) && (filter_var($value,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4))){
                $str.=$key.":".$value.".xip.io,";
            }else{
                $str.=$key.":".$value.",";
            }
        }
        if (empty($str)){
            $str="www:".$zone_name;
        }else{
            $str=substr($str,0,strlen($str)-1);
        }
        return self::zone_set($zone_name,$at,$str);
    }

    public function remove_zone_name($zone_name,$data){
        foreach ($data["hosted_cnames"] as $record => $set)
        {
            if (strlen($record) > strlen($zone_name)){
                $record2 = substr($record,0,strlen($record)-strlen($zone_name)-1);
            }else{
                $record2="@";
            }
            $data["hosted_cnames"][$record2] = $set;
            unset($data["hosted_cnames"][$record]);
        }
        foreach ($data["forward_tos"] as $record => $set)
        {
            if (strlen($record) > strlen($zone_name)){
                $record2 = substr($record,0,strlen($record)-strlen($zone_name)-1);
            }else{
                $record2="@";
            }
            $data["forward_tos"][$record2] = $set;
            unset($data["forward_tos"][$record]);
        }
        return $data;
    }

	public function reCAPTCHA($response){
        $url= "https://www.recaptcha.net/recaptcha/api/siteverify";
        $data=array (
            "secret" => reCAPTCHA_Secret,
            "response" => $response
        );
        $ch=curl_init();   
        curl_setopt($ch,CURLOPT_URL,$url);  
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);  
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);  
        $r=curl_exec($ch);  
        curl_close($ch);
        $re=json_decode($r,true);
        if (!empty($re["success"])){
            if ($re["success"]=="true"){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
	}
}

$cloudflare=new CF();