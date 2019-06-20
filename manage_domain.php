<?php

include_once("cf.class.php");

$cloudflare->is_login();

function msg($s){
    $_SESSION["msg"]=$s;
    header("Location: domains.php");
    exit(0);
}

if (empty($_GET["domain"]))
{
    msg("Domain name mustn't be empty.");
}

$re=$cloudflare->zone_lookup($_GET["domain"]);
if ($re["result"]!="success")
{
    msg("Failed to fetch data: ".$re["msg"]);
}
if ($re["response"]["zone_exists"]!=true)
{
    msg("This domain isn't managed by Cloudflare.");
}
if ($re["response"]["zone_hosted"]!=true)
{
    msg("This domain isn't managed by ".SITE_NAME.".");
}

$r=$cloudflare->remove_zone_name($re["response"]["zone_name"],$re["response"]);

include_once("header.php");
?>

  <div style="margin-top:25px" class="mdui-container">
    <div class="mdui-row">
      <div class="mdui-col-xs-12 mdui-col-sm-12 mdui-col-md-10 mdui-col-offset-md-1">

        <div class="mdui-container mdui-typo">
          <div style="float:left">
            <a href="./">Cloudflare Partners :
              <?=SITE_NAME?>
          </a> >
          <a href="domains.php">Domain Management</a> >
          <span>
            <?=$r["zone_name"]?>
          </span>
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

              <div class="mdui-table-fluid">
                <table class="mdui-table mdui-table-hoverable">
                  <thead>
                    <tr>
                      <th><button style="display:inline;" class="mdui-btn mdui-btn-icon mdui-ripple mdui-shadow-3" onclick="javascript:add_record()"><i class="mdui-icon material-icons">&#xe145;</i></button></th>
                      <th>Record</th>
                      <th>CNAME Record</th>
                      <th>Original address</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      foreach ($r["hosted_cnames"] as $record=>$set)
                      {
                        $is_ssl=false;
                        if (substr($set,strlen($set) - 12)=="comodoca.com")
                        {
                          $is_ssl=true;
                        }
                        if ((Enable_A_Record) && (filter_var(str_replace('.sslip.io','',$set),FILTER_VALIDATE_IP,FILTER_FLAG_IPV4))){
                          $set=str_replace('.sslip.io','',$set);
                        }
                        echo "<tr>".
                        '<td><button style="display:inline;" class="mdui-btn mdui-btn-icon mdui-shadow-3 ';
                        if ($is_ssl)
                        {
                          echo 'mdui-color-grey" mdui-tooltip="{content: \'Do not edit this record.\'}"';
                        }else{
                          echo 'mdui-ripple mdui-color-indigo" onclick="javascript:edit_record(\''.$record.'\',\''.$set.'\')"';
                        }
                        echo '><i class="mdui-icon material-icons">&#xe3c9;</i></button><button onclick="javascript:delete_record(\''.$record.'\')" style="display:inline;" class="mdui-btn mdui-btn-icon mdui-ripple mdui-shadow-3 mdui-color-red-900"><i class="mdui-icon material-icons">&#xe92b;</i></button></td>'.
                        "<td>".$record."</td><td>";
                        if ($is_ssl)
                        {
                          echo $set;
                        }else{
                          echo $r["forward_tos"][$record];
                        }
                        echo '</td><td>'.$set.'</td></tr>';
	                  	}
		                ?>
                  </tbody>
                </table>
              </div>
              <p>
              PS (1): Your must set a <strong>www</strong> record, or the panel will automatically set a <strong>www</strong> record whose original address is <strong><?=$r["zone_name"]?></strong>. You don't need to really set this record if you don't need it.
              </p>
              <?php if (!Enable_A_Record): ?>
              <p>
              PS (2): The original address mustn't be an IP. It should be a domain name.
              </p>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="mdui-dialog" id="delete_record">
  <div class="mdui-dialog-title">Delete record</div>
  <form method="post" action="edit_record.php" autocomplete="off">
    <div class="mdui-dialog-content">
      <input type="hidden" value="delete" name="action" />
     <input type="hidden" value="<?=$r["zone_name"]?>" name="domain" />
      <input type="hidden" value="" name="record" id="delete_record1" />Are you sure you want to delete record 
      <strong>
        <span id="delete_record2"></span>
      </strong> ?
    </div>
    <div class="mdui-dialog-actions">
      <button type="button" class="mdui-btn mdui-ripple" mdui-dialog-close>Close</button>
      <input type="submit" class="mdui-btn mdui-ripple mdui-color-red-900" value="Confirm" />
    </div>
  </form>
</div>

<div class="mdui-dialog" id="edit_record">
  <div class="mdui-dialog-title" id="edit_record_title">Edit record</div>
  <form method="post" action="edit_record.php" autocomplete="off">
    <div class="mdui-dialog-content">
      <input type="hidden" value="edit" name="action" />
     <input type="hidden" value="<?=$r["zone_name"]?>" name="domain" />
           <div class="mdui-textfield mdui-textfield-floating-label">
        <label class="mdui-textfield-label">Record</label>
        <input class="mdui-textfield-input" id ="edit_record1" name="record" required />
      </div>
     <div class="mdui-textfield mdui-textfield-floating-label">
        <label class="mdui-textfield-label">Original address</label>
        <input class="mdui-textfield-input" id="edit_record2" name="value" required />
      </div>
    </div>
    <div class="mdui-dialog-actions">
      <button type="button" class="mdui-btn mdui-ripple" mdui-dialog-close>Close</button>
      <input type="submit" class="mdui-btn mdui-ripple mdui-color-green mdui-text-color-white" value="Confirm" />
    </div>
  </form>
</div>

<script>
  function delete_record(record) { document.getElementById("delete_record2").innerHTML = record;
    document.getElementById("delete_record1").value = record;
    var inst = new mdui.Dialog("#delete_record", {
      history: false
    });
    inst.open();
  }

  function add_record(){
    document.getElementById("edit_record_title").innerHTML = "Add record";
    document.getElementById("edit_record1").value = "";
    document.getElementById("edit_record2").value = "";
    mdui.updateTextFields();
    var inst = new mdui.Dialog("#edit_record", {
      history: false
    });
    inst.open();
  }

  function edit_record(record,value){
    document.getElementById("edit_record_title").innerHTML = "Edit record";
    document.getElementById("edit_record1").value = record;
    document.getElementById("edit_record2").value = value;
    mdui.updateTextFields();
    var inst = new mdui.Dialog("#edit_record", {
      history: false
    });
    inst.open();
  }
</script>

<div class="mdui-dialog" id="msg_dialog">
  <div class="mdui-dialog-content">
    <?php 
    if (!empty($_SESSION["mng_msg"]))
    {
      echo $_SESSION["mng_msg"];
    }
    ?>
  </div>
  <div class="mdui-dialog-actions">
    <button class="mdui-btn mdui-ripple" mdui-dialog-close>Close</button>
  </div>
</div>

<?php
$msg_script=<<<SCRIPT
<script>
var inst = new mdui.Dialog("#msg_dialog",{
    history: false
});
inst.open();
</script>
SCRIPT;
if (!empty($_SESSION["mng_msg"]))
{
  echo $msg_script;
  unset($_SESSION["mng_msg"]);
}

include_once("footer.php");
?>
