<?php

function cmd_register($nick, $pass) {
    global $db;
    if (!preg_match("/^[0-9A-Z]{5,30}$/i", $nick)) {
        addchat("{db0000}[Ошибка] {FFFFFF}Ник не подходит!");
        return;
    }
    if (strlen($pass) < 5) {
        addchat("{db0000}[Ошибка] {FFFFFF}Пароль слишком короткий!");
        return;
    }
    
    $v1 = $db->query("SELECT 1 FROM `chat_blacklist` WHERE `name`='$nick'");
    $v2 = $db->query("SELECT 1 FROM `chat_users` WHERE `name`='$nick'");

    if ($v1->num_rows || $v2->num_rows) {
        addchat("{db0000}[Ошибка] {FFFFFF}Ник занят!");
        return;
    }
    
    $v3 = $db->query(sprintf("INSERT INTO `chat_users` (`name`) VALUES ('%s')", $nick));
    $id = $db->insert_id;
    $v4 = $db->query(sprintf("INSERT INTO `chat_auth` (`id`,`password`) VALUES ('%d','%s')", $id, md5($pass)));
    
    if ($user = get_user_id($id))
        session_init($user);
}

function cmd_login($nick, $pass) {

    $user = find_user('name', $nick);
    if ($user == NULL) {
        addchat("{db0000}[Ошибка] {ffffff}Аккаунт не существует!");
        return;
    }
    
    $auth = get_auth_id($user->id);
    if ($auth == NULL) {
        addchat("{db0000}[Ошибка] {FFFFFF}Нет данных для авторизации!");
        return;
    }
    
    if ($auth->password != md5($pass)) {
        addchat("{db0000}[Ошибка] {FFFFFF}Пароль неправильный!");
        return;
    }
    
    session_init($user);
}

function format_out(&$input) {
    
    if (strlen($input) > 192)
        $input = substr($input, 0, 192);
    
    $input = preg_replace("/[^\\x20-\\x7FА-ЯЁа-яё]/u","", $input);
    $input = trim($input);
    
    if ($GLOBALS["USER"]->status < 2) {
        do {
            $input = preg_replace("/{[0-9A-F]{6}}/i", "", $input, -1, $pc);
        } while ($pc != 0);
    }
    
    if (strlen($input) == 0)
        return false;
    
    foreach ([
        [":)", "\u{f118}"], [">:(","\u{f556}"], [":|", "\u{f11a}"], // [":/", "\u{f11a}"],
        [";)", "\u{f58c}"], [":(", "\u{f119}"], [":D", "\u{f599}"], [":'(","\u{f5b4}"],
        [":o", "\u{f5c2}"], [":p", "\u{f58a}"], ["=)", "\u{f581}"], ["xD", "\u{f586}"],
        ["(r)","\u{f25d}"], ["(c)","\u{f1f9}"], ["(+)","\u{f164}"], ["(-)","\u{f165}"],
        ["(*)","\u{f005}"], ["($)","\u{f3d1}"], ["(%)","\u{f3a5}"], ["<3", "\u{f004}"],
        ["0_0","\u{f579}"], [":*", "\u{f598}"]
    ] as $pair)
        $input = str_ireplace($pair[0], $pair[1], $input);
    
    return true;
}

function cmd_say($text) {
    if (strlen($text) && format_out($text))
        addmsg($GLOBALS["USER"]->id, 0, 1, $text);
}

function cmd_setpass($pass) {
    global $db, $USER;
    $QUERY_SETPASS = "UPDATE `chat_auth` SET `password`='%s' WHERE `id`=%d";
    
    if (strlen($pass) < 5) {
        addchat("{db0000}[Ошибка] {FFFFFF}Пароль слишком короткий!");
        return;
    }

    if ($db->query(sprintf($QUERY_SETPASS, md5($pass), $USER->id)))
        addchat("{9cff9f}Пароль изменён на {FFFFFF}\"$pass\"{9cff9f}. Используйте {FFFFFF}F8 {9cff9f}чтобы сохранить.");
}

function cmd_online() {
    global $db, $USER;
    $QUERY_ONLINE = "SELECT `id`,`prefix`,`name`,`status`,`authtime` FROM `chat_users` WHERE `authtime`>%d ORDER BY `authtime` DESC";
    
    addchat("Пользователи онлайн:");
    $time = (time() - 300);
    if ($v1 = $db->query(sprintf($QUERY_ONLINE, $time)))
        while ($v2 = $v1->fetch_object())
            if ($v2->status < 4 || $USER->status > 3) {
                $_time = 300 - ($v2->authtime - $time);
                if ($_time < 60)
                    addchat("%s%s[%d] (%d) (%d сек. назад)", $v2->prefix, $v2->name, $v2->id, $v2->status, $_time);
                else
                    addchat("%s%s[%d] (%d) (%d мин. назад)", $v2->prefix, $v2->name, $v2->id, $v2->status, $_time / 60);
            }
}

function cmd_help() {
    addchat("{ffffff}Доступные команды:");
    addchat("{ffe500}Без категории:");
    addchat("{ffe500}- /passwd (/setpass) {ffffff}<password> {ffe500}- смена пароля");
    addchat("{ffe500}- /сс - очистить чат");
    addchat("{ffe500}- /ping - Pong!");
    addchat("{62ff00}Общение:");
    addchat("{62ff00}- {ffffff}<text> {62ff00}- написать в общий чат");
    addchat("{62ff00}- /online - список пользователей онлайн");
    addchat("{62ff00}- /pm (/msg) {ffffff}<id> <text> {62ff00}- личное сообщение");
    addchat("{62ff00}- /re (/r) {ffffff}<text> {62ff00}- ответить в лс");
}

function cmd_exit() { session_free(); }
function cmd_ping() { addchat("{f7f488}Pong!"); }
function cmd_time() { addchat("{f7f488}Время: {ffffff}%s", date("H:i:s")); }

function cmd_msg($id, $text) {
    global $USER;
    if ($id == $USER->id)
        return;
    
    $user = get_user_id($id);
    if ($user == NULL || ($user->authtime + 300) < time()) {
        addchat("{db0000}[Ошибка] {ffffff}Пользователь оффлайн!");
        return;
    }
    
    if (format_out($text))
        addmsg($USER->id, $id, 7, $text);
}

function cmd_msg_re($text) {
    global $db, $USER;
    $QUERY_GET_RE = "SELECT `u0`,`u1` FROM `chat_log` WHERE `type`='7' AND `time`>'%d' AND (`u0`='%d' OR `u1`='%d') ORDER BY `id` DESC LIMIT 1";
    
    $id = 0;
    if ($v1 = $db->query(sprintf($QUERY_GET_RE, (time() - 300), $USER->id, $USER->id)))
        if ($v2 = $v1->fetch_object())
            $id = ($v2->u0 == $USER->id) ? $v2->u1 : $v2->u0;
    
    if ($id == 0) {
        addchat("{db0000}[Ошибка] {ffffff}Некому отвечать");
        return;
    }
    
    if (format_out($text))
        addmsg($USER->id, $id, 7, $text);
}

function cmd_ban($id, $reason) {
    global $db, $USER;
    
    $user = get_user_id($id);
    if ($user == NULL) {
        addchat("{db0000}[Ошибка] {FFFFFF}Пользователь не найден!");
        return;
    }
    
    if ($USER->status < 4 && ($user->authtime + 300) < time()) {
        addchat("{db0000}[Ошибка] {FFFFFF}Пользователь оффлайн!");
        return;
    }
    
    if ($user->status >= $USER->status) {
        addchat("{db0000}[Ошибка] {FFFFFF}Нет доступа!");
        return;
    }

    if ($db->query("UPDATE `chat_users` SET `status`=0 WHERE `id`=$id"))
        addmsg($USER->id, $id, 5, $reason);
}

function cmd_clear() {
    pushevent("erase;");
    addchat("{f7f488}Чат очищен.");
}
function cmd_clear_g() {
    $GLOBALS["db"]->query("DELETE FROM `chat_log`");
    addevent_g("erase;");
    addmsg(0, 0, 0, "{f02e2e}Чат очищен администратором.");
}

function cmd_clear_u($id) {
    global $db;
    if ($v1 = $db->query("SELECT `name` FROM `chat_users` WHERE `id`='$id'"))
        if ($v2 = $v1->fetch_object())
            addevent_g("erase=$v2->name[$id]");
    if ($db->query("DELETE FROM `chat_log` WHERE `u0`='$id'"))
        addchat("Удалено $db->affected_rows сообщений.");
}

function cmd_erase($text) {
    addevent_g("erase=$text;");
    addchat("ok");
}

function cmd_update() {
    pushevent("erase;");
    $GLOBALS["USER"]->lastmsg = 0;
}

function cmd_destroy($id) {
    global $db;
    $user = get_user_id($id);
    if ($user == NULL) {
        addchat("{db0000}[Ошибка] {FFFFFF}Пользователь не найден!");
        return;
    }
    
    if ($db->query("UPDATE `chat_users` SET `status`=0 WHERE `id`=$id"))
    {
        cmd_clear_u($id);
        addchat("{db0000}>> %s%s[%d] {db0000}уничтожен.", $user->prefix, $user->name, $id);
    }
}

function cmd_emul($str) {
	pushevent("$str");
	addchat("{ff00ff}emul: $str");
}

$CMD = [
    [ 0, "cmd_register",["reg","register"],     [ "nick" => "s", "pass" => "s"] ],
    [ 0, "cmd_login",   ["auth","login"],       [ "nick" => "s", "pass" => "s"] ],
    [ 1, "cmd_help",    ["help"] ],
    [ 1, "cmd_ping",    ["ping"] ],
    [ 1, "cmd_time",    ["time"] ],
    [ 1, "cmd_online",  ["online"] ],
    [ 2, "cmd_exit",    ["exit"] ],
    [ 1, "cmd_setpass", ["passwd","setpass"],   [ "pass" => "s" ] ],
    [ 1, "cmd_msg",     ["msg","pm","sms"],     [ "id" => "d", "text" => "*" ] ],
    [ 1, "cmd_msg_re",  ["re","r"],             [ "text" => "*" ] ],
    [ 1, "cmd_clear",   ["cc"] ],
    [ 3, "cmd_ban",     ["ban"],                [ "id" => "d", "reason" => "*" ] ],
    [ 4, "cmd_clear_g", ["clear"] ],
    [ 4, "cmd_clear_u", ["clear_user"],         [ "id" => "d" ] ],
    [ 4, "cmd_erase",   ["erase"],              [ "text" => "*" ] ],
	[ 4, "cmd_emul",    ["emul"],               [ "text" => "*" ] ],
    [ 2, "cmd_update",  ["update"] ],
    [ 4, "cmd_destroy", ["destroy"],            [ "id" => "d" ] ],
];

function get_cmd($n) {
    foreach ($GLOBALS["CMD"] as $v1)
        foreach($v1[2] as $v2)
            if ($v2 == $n)
                return $v1;
    return null;
}

function get_args($cmd, $args, &$out) {
    if (!isset($cmd[3]))
        return true;
    
    $fmt = "";
    foreach ($cmd[3] as $k => $v) {
        if (!empty($fmt))
            $fmt .= " ";
        switch ($v) {
        case "d": $fmt .= "%d";     break;
        case "s": $fmt .= "%s";     break;
        case "*": $fmt .= "%[^[]]"; break;
        }
    }
    
    $out = sscanf($args, $fmt);
    return !($out == null || in_array(null, $out, true));
}

function get_usage($cmd) {
    $args = "";
    foreach ($cmd[3] as $k => $v)
        $args .= " <$k>";
    return "{db0000}Используйте: /" . $cmd[2][0] . $args;
}

function chat_oninput($input) {
    global $USER;
    if (sscanf($input, "/%s %[^[]]", $cmd, $arg) == 0) {
        if ($USER)
            cmd_say($input);
        else
            addchat("{db0000}[Ошибка] {ffffff}Требуется авторизация!");
        return;
    }

    $cmd = get_cmd($cmd);
    if ($cmd == null) {
        addchat("{db0000}[Ошибка] {FFFFFF}Неизвестная команда.");
        return;
    }
    
    if ($USER == null) {
        if ($cmd[0] != 0) {
            addchat("{db0000}[Ошибка] {ffffff}Требуется авторизация!");
            return;
        }
    } else if ($cmd[0] == 0 || $USER->status < $cmd[0]) {
        addchat("{db0000}[Ошибка] {ffffff}Нет доступа!");
        return;
    }
    
    if (get_args($cmd, $arg, $args)) {
        if ($args)
            $cmd[1](...$args);
        else
            $cmd[1]();
    } else {
        addchat(get_usage($cmd));
    }
}
