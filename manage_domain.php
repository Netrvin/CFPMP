<?php

include_once("cf.class.php");

$cloudflare->is_login();

function msg($s)
{
    $_SESSION["msg"] = $s;
    header("Location: domains.php");
    exit(0);
}

if (empty($_GET["domain"])) {
    msg("域名不能为空");
}

$re = $cloudflare->zone_lookup($_GET["domain"]);
if ($re["result"] != "success") {
    msg("查询失败：" . $re["msg"]);
}
if ($re["response"]["zone_exists"] != true) {
    msg("该域名未在Cloudflare接入");
}
if ($re["response"]["zone_hosted"] != true) {
    msg("该域名未在" . SITE_NAME . "接入");
}

$r = $cloudflare->get_proxied_records($_GET["domain"]);
$zone_id = $cloudflare->get_zone_id($_GET["domain"]);

if (!$r["success"]) {
    msg("查询失败：" . $r["errors"][0]["message"]);
}

include_once("header.php");
?>

<div style="margin-top:25px" class="mdui-container">
    <div class="mdui-row">
        <div class="mdui-col-xs-12 mdui-col-sm-12 mdui-col-md-10 mdui-col-offset-md-1">

            <div class="mdui-container mdui-typo">
                <div style="float:left">
                    <a href="./">Cloudflare Partners :
                        <?= SITE_NAME ?>
                    </a> >
                    <a href="domains.php">域名管理</a> >
                    <span>
            <?= $re['response']["zone_name"] ?>
          </span>
                    <br/>
                    <br/>
                </div>
                <div style="float:right">
                    <?= $_SESSION["email"] ?>.
                    <a href="./">登出</a>
                </div>
            </div>

            <div class="mdui-card mdui-shadow-5">
                <div class="mdui-card-content">
                    <div class="mdui-container mdui-typo">

                        <div class="mdui-table-fluid">
                            <table class="mdui-table mdui-table-hoverable">
                                <thead>
                                <tr>
                                    <th>
                                        <button style="display:inline;"
                                                class="mdui-btn mdui-btn-icon mdui-ripple mdui-shadow-3"
                                                onclick="javascript:add_record()"><i class="mdui-icon material-icons">&#xe145;</i>
                                        </button>
                                    </th>
                                    <th>记录</th>
                                    <th>CNAME记录</th>
                                    <th>回源地址</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($r["result"] as $index => $data) {
                                    if ((Enable_A_Record) && (filter_var(str_replace('.sslip.io', '', $data['content']), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))) {
                                        $data['content'] = str_replace('.sslip.io', '', $data['content']);
                                    }
                                    echo "<tr>" .
                                        '<td><button style="display:inline;" class="mdui-btn mdui-btn-icon mdui-shadow-3 ';
                                    if ($is_ssl) {
                                        echo 'mdui-color-grey" mdui-tooltip="{content: \'请勿修改SSL配置记录\'}"';
                                    } else {
                                        echo 'mdui-ripple mdui-color-indigo" onclick="javascript:edit_record(\'' . $data['id'] . '\',\'' . substr($data['name'],0,strlen($data['name'])-strlen($data['zone_name'])-1) . '\',\'' . $data['content'] . '\')"';
                                    }
                                    echo '><i class="mdui-icon material-icons">&#xe3c9;</i></button><button onclick="javascript:delete_record(\'' . $data['id'] . '\',\'' . $data['name'] .'\')" style="display:inline;" class="mdui-btn mdui-btn-icon mdui-ripple mdui-shadow-3 mdui-color-red-900"><i class="mdui-icon material-icons">&#xe92b;</i></button></td>' .
                                        "<td>" . substr($data['name'],0,strlen($data['name'])-strlen($data['zone_name'])-1) . "</td><td>";
                                    echo $data['name'].'.cdn.cloudflare.net';
                                    echo '</td><td>' . $data['content'] . '</td></tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <p>
                            注
                            (1)：必须设置一个<strong>www</strong>记录，否则会自动设置一个回源地址为<strong><?= $r["zone_name"] ?></strong>的<strong>www</strong>记录。本记录可不在DNS服务商配置
                        </p>
                        <?php if (!Enable_A_Record): ?>
                            <p>
                                注 (3)：回源地址以<strong>CNAME</strong>形式填写，暂时不支持<strong>A</strong>记录和<strong>AAAA</strong>记录
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="mdui-dialog" id="delete_record">
    <div class="mdui-dialog-title">删除记录</div>
    <form method="post" action="edit_record.php" autocomplete="off">
        <div class="mdui-dialog-content">
            <input type="hidden" value="delete" name="action"/>
            <input type="hidden" value="<?= $zone_id ?>" name="zone_id"/>
            <input type="hidden" value="<?= $re['response']["zone_name"] ?>" name="domain"/>
            <input type="hidden" value="" id="delete_record_id" name="record_id"/>
            <input type="hidden" value="" name="record" id="delete_record1"/> 你确定要删除记录
            <strong>
                <span id="delete_record2"></span>
            </strong>吗？
        </div>
        <div class="mdui-dialog-actions">
            <button type="button" class="mdui-btn mdui-ripple" mdui-dialog-close>关闭</button>
            <input type="submit" class="mdui-btn mdui-ripple mdui-color-red-900" value="确定"/>
        </div>
    </form>
</div>

<div class="mdui-dialog" id="edit_record">
    <div class="mdui-dialog-title" id="edit_record_title">修改记录</div>
    <form method="post" action="edit_record.php" autocomplete="off">
        <div class="mdui-dialog-content">
            <input type="hidden" value="edit" id="edit_record_type" name="action"/>
            <input type="hidden" value="<?= $zone_id ?>" name="zone_id"/>
            <input type="hidden" value="<?= $re['response']["zone_name"] ?>" name="domain"/>
            <input type="hidden" value="" id="edit_record_id" name="record_id"/>
            <div class="mdui-textfield mdui-textfield-floating-label">
                <label class="mdui-textfield-label">记录</label>
                <input class="mdui-textfield-input" id="edit_record1" name="record" required/>
            </div>
            <div class="mdui-textfield mdui-textfield-floating-label">
                <label class="mdui-textfield-label">回源地址</label>
                <input class="mdui-textfield-input" id="edit_record2" name="value" required/>
            </div>
        </div>
        <div class="mdui-dialog-actions">
            <button type="button" class="mdui-btn mdui-ripple" mdui-dialog-close>关闭</button>
            <input type="submit" class="mdui-btn mdui-ripple mdui-color-green mdui-text-color-white" value="确定"/>
        </div>
    </form>
</div>

<script>
    function delete_record(id, record) {
        document.getElementById("delete_record2").innerHTML = record;
        document.getElementById("delete_record1").value = record;
        document.getElementById("delete_record_id").value = id;
        var inst = new mdui.Dialog("#delete_record", {
            history: false
        });
        inst.open();
    }

    function add_record() {
        document.getElementById("edit_record_title").innerHTML = "添加记录";
        document.getElementById("edit_record1").value = "";
        document.getElementById("edit_record2").value = "";
        document.getElementById("edit_record_type").value = "add";
        //document.getElementById("edit_record1").removeAttribute("disabled");
        mdui.updateTextFields();
        var inst = new mdui.Dialog("#edit_record", {
            history: false
        });
        inst.open();
    }

    function edit_record(id, record, value) {
        document.getElementById("edit_record_title").innerHTML = "修改记录";
        document.getElementById("edit_record1").value = record;
        //document.getElementById("edit_record1").setAttribute("disabled", "true");
        document.getElementById("edit_record2").value = value;
        document.getElementById("edit_record_id").value = id;
        document.getElementById("edit_record_type").value = "edit";
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
        if (!empty($_SESSION["mng_msg"])) {
            echo $_SESSION["mng_msg"];
        }
        ?>
    </div>
    <div class="mdui-dialog-actions">
        <button class="mdui-btn mdui-ripple" mdui-dialog-close>关闭</button>
    </div>
</div>

<?php
$msg_script = <<<SCRIPT
<script>
var inst = new mdui.Dialog("#msg_dialog",{
    history: false
});
inst.open();
</script>
SCRIPT;
if (!empty($_SESSION["mng_msg"])) {
    echo $msg_script;
    unset($_SESSION["mng_msg"]);
}

include_once("footer.php");
?>
