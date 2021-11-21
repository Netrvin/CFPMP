<?php

include_once("cf.class.php");

$cloudflare->is_login();

$r = $cloudflare->user_lookup();

include_once("header.php");

$output = '';
?>

<div style="margin-top:25px" class="mdui-container">
    <div class="mdui-row">
        <div class="mdui-col-xs-12 mdui-col-sm-12 mdui-col-md-10 mdui-col-offset-md-1">

            <div class="mdui-container mdui-typo">
                <div style="float:left">
                    <a href="./">Cloudflare Partners :
                        <?= SITE_NAME ?>
                    </a> >
                    <span>域名管理</span>
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
                        <button mdui-dialog="{target: '#add_domain'}" style="display:inline"
                                class="mdui-btn mdui-ripple mdui-shadow-4">添加域名
                        </button>

                        <?php if (($r["result"] == "success") && (!empty($r["response"]["hosted_zones"]))): ?>
                            <br/>
                            <br/>
                            <div class="mdui-table-fluid">
                                <table class="mdui-table">
                                    <thead>
                                    <tr>
                                        <th>域名</th>
                                        <th class="mdui-table-col-numeric"></th>
                                        <th class="mdui-table-col-numeric"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($r["response"]["hosted_zones"] as $value) {
                                        $output .= '
            <tr>
            <td>' . $value . '</td>
            <td><a href="manage_domain.php?domain=' . $value . '">管理</a></td>
            <td><a href="" onclick="javascript:delete_domain(\'' . $value . '\');return false;">删除</a></td>
            </tr>
            ';
                                    }
                                    echo $output;
                                    ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php elseif ($r["result"] == "error"): ?>
                            <p>查询失败：
                            <?= $r["msg"]; ?>

                        <?php else: ?>
                            <p>无域名，赶紧添加一个吧！</p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="mdui-dialog" id="add_domain">
    <div class="mdui-dialog-title">添加域名</div>
    <form method="post" action="add_domain.php" autocomplete="off">
        <div class="mdui-dialog-content">
            <div class="mdui-textfield mdui-textfield-floating-label">
                <label class="mdui-textfield-label">域名</label>
                <input class="mdui-textfield-input" name="domain" required/>
            </div>
            <?php if (Enable_TXT_Verification): ?>
            <p class="mdui-typo" style="text-align: center;">
                请在此域名添加名称为 cfpmp 的 TXT 记录<code style="display: block;"><?=password_hash(Random_String.$_SESSION["email"],PASSWORD_BCRYPT)?></code>此记录可在验证完毕后删除
            </p>
            <p class="mdui-typo" style="text-align: center; color: red;">
                因 Cloudflare Host API 更新，可能无法添加新域名。
            </p>
            <?php endif; ?>
        </div>
        <div class="mdui-dialog-actions">
            <button type="button" class="mdui-btn mdui-ripple" mdui-dialog-close>关闭</button>
            <input type="submit" class="mdui-btn mdui-ripple" value="添加"/>
        </div>
    </form>
</div>

<div class="mdui-dialog" id="delete_domain">
    <div class="mdui-dialog-title">删除域名</div>
    <form method="post" action="delete_domain.php" autocomplete="off">
        <div class="mdui-dialog-content">
            <input type="hidden" value="" name="domain" id="delete_domainname1"/> 你确定要删除域名
            <strong>
                <span id="delete_domainname2"></span>
            </strong>吗？
        </div>
        <div class="mdui-dialog-actions">
            <button type="button" class="mdui-btn mdui-ripple" mdui-dialog-close>关闭</button>
            <input type="submit" class="mdui-btn mdui-ripple" value="确定"/>
        </div>
    </form>
</div>

<div class="mdui-dialog" id="msg_dialog">
    <div class="mdui-dialog-content">
        <?php
        if (!empty($_SESSION["msg"])) {
            echo $_SESSION["msg"];
        }
        ?>
    </div>
    <div class="mdui-dialog-actions">
        <button class="mdui-btn mdui-ripple" mdui-dialog-close>关闭</button>
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
$msg_script = <<<SCRIPT
<script>
var inst = new mdui.Dialog("#msg_dialog",{
    history: false
});
inst.open();
</script>
SCRIPT;
if (!empty($_SESSION["msg"])) {
    echo $msg_script;
    unset($_SESSION["msg"]);
}

include_once("footer.php");
?>
