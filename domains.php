<?php

include_once("cf.class.php");

$cloudflare->is_login();

$r=$cloudflare->user_lookup();

include_once("header.php");

$output = '';
?>

<div style="margin-top:25px" class="mdui-container">
  <div class="mdui-row">
    <div class="mdui-col-xs-12 mdui-col-sm-12 mdui-col-md-10 mdui-col-offset-md-1">

      <div class="mdui-container mdui-typo">
       <div style="float:left">
         <a href="./">Cloudflare Partners :
            <?=SITE_NAME?>
          </a> >
          <span>Domains Management</span>
          <br />
          <br />
        </div>
        <div style="float:right">
         <?=$_SESSION["email"]?>. 
         <a href="./">Sign out</a>
        </div>
      </div>


      <div class="mdui-card mdui-shadow-5">
        <div class="mdui-card-content">
          <div class="mdui-container mdui-typo">
            <button mdui-dialog="{target: '#add_domain'}" style="display:inline" class="mdui-btn mdui-ripple mdui-shadow-4">Add domain</button>

            <?php if (($r["result"]=="success")&&(!empty($r["response"]["hosted_zones"]))): ?>
            <br />
            <br />
            <div class="mdui-table-fluid">
              <table class="mdui-table">
                <thead>
                  <tr>
                    <th>Domain</th>
                    <th class="mdui-table-col-numeric"></th>
                    <th class="mdui-table-col-numeric"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
            foreach ($r["response"]["hosted_zones"] as $value) {
            $output.='
            <tr>
            <td>'.$value.'</td>
            <td><a href="manage_domain.php?domain='.$value.'">Manage</a></td>
            <td><a href="" onclick="javascript:delete_domain(\''.$value.'\');return false;">Delete</a></td>
            </tr>
            ';
        }
        echo $output;
      ?>
                </tbody>
              </table>
            </div>

            <?php elseif ($r["result"]=="error"): ?>
            <p>Failed to fetch domains: 
              <?=$r["msg"];?>

                <?php else: ?>
                <p>No domains.</p>
                <?php endif; ?>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<div class="mdui-dialog" id="add_domain">
  <div class="mdui-dialog-title">Add domain</div>
  <form method="post" action="add_domain.php" autocomplete="off">
    <div class="mdui-dialog-content">
      <div class="mdui-textfield mdui-textfield-floating-label">
        <label class="mdui-textfield-label">Domain</label>
        <input class="mdui-textfield-input" name="domain" required />
      </div>
    </div>
    <div class="mdui-dialog-actions">
      <button type="button" class="mdui-btn mdui-ripple" mdui-dialog-close>Close</button>
      <input type="submit" class="mdui-btn mdui-ripple" value="Add" />
    </div>
  </form>
</div>

<div class="mdui-dialog" id="delete_domain">
  <div class="mdui-dialog-title">Delete domain</div>
  <form method="post" action="delete_domain.php" autocomplete="off">
    <div class="mdui-dialog-content">
      <input type="hidden" value="" name="domain" id="delete_domainname1" />Are you sure you want to delete domain 
      <strong>
        <span id="delete_domainname2"></span>
      </strong> ?
    </div>
    <div class="mdui-dialog-actions">
      <button type="button" class="mdui-btn mdui-ripple" mdui-dialog-close>Close</button>
      <input type="submit" class="mdui-btn mdui-ripple" value="Confirm" />
    </div>
  </form>
</div>

<div class="mdui-dialog" id="msg_dialog">
  <div class="mdui-dialog-content">
    <?php 
    if (!empty($_SESSION["msg"])){
      echo $_SESSION["msg"];
    }
    ?>
  </div>
  <div class="mdui-dialog-actions">
    <button class="mdui-btn mdui-ripple" mdui-dialog-close>Close</button>
  </div>
</div>

<script>
  function delete_domain(domain) {
    document.getElementById("delete_domainname2").innerHTML = domain;
    document.getElementById("delete_domainname1").value = domain;
    var inst = new mdui.Dialog("#delete_domain", {
      history: false
    });
    inst.open();
  }
</script>

<?php
$msg_script=<<<SCRIPT
<script>
var inst = new mdui.Dialog("#msg_dialog",{
    history: false
});
inst.open();
</script>
SCRIPT;
if (!empty($_SESSION["msg"])){
  echo $msg_script;
  unset($_SESSION["msg"]);
}

include_once("footer.php");
?>
