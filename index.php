<?php

include_once("cf.class.php");

$cloudflare->logout();

include_once("header.php");
?>

<div style="margin-top:25px" class="mdui-container">
  <div class="mdui-row">
    <div class="mdui-col-xs-12 mdui-col-offset-sm-3 mdui-col-offset-md-4 mdui-col-sm-6 mdui-col-md-4">

      <div class="mdui-container mdui-typo">
        <div style="float:left">
            Cloudflare Partners :
            <?=SITE_NAME?>
            <br />
            <br />
        </div>
      </div>

      <div class="mdui-card mdui-shadow-5">
        <div class="mdui-card-content">
          <div class="mdui-container mdui-typo">
            <form method="post" action="auth.php">

              <center>
                <h2>登入
                  <?php echo SITE_NAME; ?>
                </h2>
              </center>
              <div class="mdui-textfield mdui-textfield-floating-label">
                <label class="mdui-textfield-label">Cloudflare 邮箱</label>
                <input class="mdui-textfield-input" name="email" type="email" />
              </div>

              <div class="mdui-textfield mdui-textfield-floating-label">
                <label class="mdui-textfield-label">Cloudflare 密码</label>
                <input class="mdui-textfield-input" name="password" type="password" />
              </div>

              <?php if (Enable_reCAPTCHA): ?>
              <div class="g-recaptcha" data-sitekey="<?php echo reCAPTCHA_Site; ?>"></div>
              <?php endif; ?>

              <div class="mdui-row-xs-1">
                <div class="mdui-col">
                  <input type="submit" class="mdui-btn mdui-btn-block mdui-btn-raised mdui-ripple" value="登录 / 注册" />
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<div class="mdui-dialog" id="error">
  <div class="mdui-dialog-content">
    <?php if (!empty($_SESSION["login_msg"])){echo $_SESSION["login_msg"];}?>
  </div>
  <div class="mdui-dialog-actions">
    <button class="mdui-btn mdui-ripple" mdui-dialog-close>关闭</button>
  </div>
</div>

<?php
$error=<<<SCRIPT
<script>
var inst = new mdui.Dialog("#error",{
    history: false
});
inst.open();
</script>
SCRIPT;
if (!empty($_SESSION["login_msg"]))
{
  echo $error;
  unset($_SESSION["login_msg"]);
}
include_once("footer.php");
?>