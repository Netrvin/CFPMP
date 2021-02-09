<?php

// Cloudflare Partners host key 
define("HOST_KEY", "");

// 站点名称
define("SITE_NAME", "");

// reCAPTCHA配置
define("Enable_reCAPTCHA", false);
define("reCAPTCHA_Site", "");
define("reCAPTCHA_Secret", "");

//A记录解析（基于sslip.io）
define("Enable_A_Record", false);

//允许用户通过面板直接注册
define("Allow_Register", false);

//域名添加时验证 TXT 记录
define("Enable_TXT_Verification", true);
define("Random_String", "");  // 请务必设置，且保证至少64位并足够复杂