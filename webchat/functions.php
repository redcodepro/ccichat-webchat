<?php

$_chat_chat = [];
$_chat_user = [];

function pushchat(&$msg, ...$va) {
	global $_chat_chat, $USER;
    $_chat_chat[] = [ "m" => ($USER->status > 3 ? "[id:$msg->id] " : "") . vsprintf($msg->fmt, $va), "i" => intval($msg->id), "t" => intval($msg->time) ];
}
function addchat($fmt, ...$va) {
    $GLOBALS["_chat_user"][] = [ "m" => vsprintf($fmt, $va) ];
}
function addevent($fmt, ...$va) {
    $GLOBALS["_chat_user"][] = [ "e" => vsprintf($fmt, $va) ];
}
function pushevent($event) {
    $GLOBALS["_chat_chat"][] = [ "e" => $event ];
}

function chat_exit() {
    $response = ["data"=>array_merge($GLOBALS["_chat_chat"],$GLOBALS["_chat_user"])];
    $response['hud'] = get_online();
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function create_key($len) {
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $key = "";
    while ($len--)
        $key .= $chars[random_int(0, 61)];
	return $key;
}

function find_user($key, $value) {
    global $db;
    $QUERY_GET_USER = "SELECT * FROM `chat_users` WHERE `%s`='%s'";
    if ($v1 = $db->query(sprintf($QUERY_GET_USER, $key, $db->escape_string($value))))
        return $v1->fetch_object();
    return NULL;
}

function get_auth_id($id) {
    if ($v1 = $GLOBALS["db"]->query("SELECT * FROM `chat_auth` WHERE `id`='$id'"))
        return $v1->fetch_object();
    return NULL;
}

function get_user_id($id) {
    if ($v1 = $GLOBALS["db"]->query("SELECT * FROM `chat_users` WHERE `id`='$id'"))
        return $v1->fetch_object();
    return NULL;
}

function find_reauth($restore) {
    if (preg_match("/^[A-Z0-9]{64}$/i", $restore) != 1)
        return NULL;
    if ($v1 = $GLOBALS["db"]->query("SELECT * FROM `chat_auth` WHERE `restore`='$restore'"))
        if ($v2 = $v1->fetch_object()) {
            $_COOKIE['1'] = $v2->id;
            $_COOKIE['2'] = $v2->session;
            setcookie("1", $v2->id);
            setcookie("2", $v2->session);
            return $v2;
        }
    return NULL;
}

function find_auth_user() {
    $auth = NULL;
    if (isset($_GET['code']))
        $auth = find_reauth($_GET['code']);
    
    if (empty($_COOKIE['1']) || empty($_COOKIE['2']))
        return NULL;
    
    if ($auth == NULL)
        $auth = get_auth_id(intval($_COOKIE['1']));
    
    if ($auth && $auth->session == $_COOKIE['2']) {
        if ($user = get_user_id($auth->id)) {
            if ($user->status == 0) {
                addevent("state=0;");
                chat_exit();
            }
            if (empty($_COOKIE['0']))
            {
                setcookie("0", "1");
                addevent("state=2;active=5000;");
                $user->lastmsg = 0;
                if ($user->status < 4)
                    addmsg($user->id, 0, 3, "");
            }
            return $user;
        }
    }
    
    session_free();
    return NULL;
}

function session_init($user) {
    global $db, $USER;
    $QUERY_V1 = "UPDATE `chat_users` SET `authtime`='%d',`ip`='%s' WHERE `id`='%d';";
    $QUERY_V2 = "UPDATE `chat_auth` SET `session`='%s',`restore`='%s' WHERE `id`='%d';";
    
    $USER = $user;
    $USER->lastmsg = 0;
    
    $session = create_key(32);
    $restore = create_key(64);
    
    $db->query(sprintf($QUERY_V1, time(), $_SERVER['REMOTE_ADDR'], $USER->id));
    $db->query(sprintf($QUERY_V2, $session, $restore, $USER->id));

    addevent("code=$restore;state=2;active=5000;");
    setcookie("1", $USER->id);
    setcookie("2", $session);
    
    if ($USER->status < 4)
        addmsg($USER->id, 0, 2, "");
}

function session_free() {
    addevent("code=;state=1;");
    setcookie("1", "");
    setcookie("2", "");
    addchat("{db0000}Disconnected.");
}

function addmsg($id_0, $id_1, $type, $text) {
    global $db, $USER;
    $QUERY_CHAT = "INSERT INTO `chat_log` (`time`,`type`,`u0`,`u1`,`text`) VALUES ('%d','%d','%d','%d','%s');";
    $db->query(sprintf($QUERY_CHAT, time(), $type, $id_0, $id_1, $db->escape_string($text)));
}

function addevent_g($fmt, ...$va) {
    addmsg(0, 0, 8, vsprintf($fmt, $va));
}

function get_online()
{
    global $db, $USER;
    $QUERY_ONLINE = "SELECT `id`,`prefix`,`name`,`status`,`authtime` FROM `chat_users` WHERE `authtime`>%d ORDER BY `authtime` DESC";
    
    if ($USER == NULL || $USER->status < 3)
        return NULL;
    
    $time = (time() - 120);
    $v1 = $db->query(sprintf($QUERY_ONLINE, $time));
    if ($v1 == false)
        return NULL;
    
    $out = "Онлайн: " . date("H:m:s");
    while ($v2 = $v1->fetch_object())
    {
        if ($v2->status >= 4 && $USER->status < 4)
            continue;
        
        $_time = 120 - ($v2->authtime - $time);
        $out .= sprintf("\n%s%s[%d] (%d)", $v2->prefix ?? "{ffffff}", $v2->name, $v2->id, $v2->status);
        $out .= $_time > 5 ? " ($_time сек. назад)" : "";
    }
    return $out;
}
