<?php
include "functions.php";

//addevent("state=0;");
//addchat("~~~");
//chat_exit();

if ($_SERVER['HTTP_USER_AGENT'] != "WebChat/1.4") {
    addevent("state=0;");
    addchat("{db0000}Клиент не поддерживается!");
	//addchat("Обновление временно не доступно.");
    addchat("Используйте: \u{f35a}\u{f35a} https://www.blast.hk/threads/134688/ \u{f359}\u{f359}");
    chat_exit();
}

$db = new mysqli("localhost", "user", "password", "webchat");
if ($db->connect_errno != 0)
    return;

$USER = find_auth_user();

if ($USER == null && !isset($_COOKIE['0'])) {
    setcookie("0", "1");
    addevent("state=1;active=5000;");
    addchat("{fffa66}Подключен к: {ffffff}Web Chat v1.4c ({9ea8ff}php{ffffff})");
	addchat("");
    addchat("{fffa66}Для входа используйте команды:");
    addchat("{fffa66}  /auth {ffffff}<nick> <password> {fffa66}- для авторизации");
    addchat("{fffa66}  /register {ffffff}<nick> <password> {fffa66}- для регистрации");
}

include "commands.php";
$input = file_get_contents('php://input');
if (strlen($input) != 0)
    chat_oninput($input);
if ($USER != null)
    chat_update();
chat_exit();

// =================================================
// functions

function nick($a, $b) {
    return $b ? "$a->p1$a->n1[$a->u1]" : "$a->p0$a->n0[$a->u0]";
}

function chat_update()
{
    global $db, $USER;
    $QUERY_GET_UNREADED =
"(select `log`.*,`a0`.`prefix` as `p0`,`a0`.`name` as `n0`,`a1`.`prefix` as `p1`,`a1`.`name` as `n1`,`chat_format`.`fmt`
from `chat_log` as `log`
inner join `chat_format` on `chat_format`.`id`=`log`.`type`
inner join `chat_users` as `a0` on `a0`.`id`=`log`.`u0`
inner join `chat_users` as `a1` on `a1`.`id`=`log`.`u1`
where `log`.`id`>'%d' order by `log`.`id` desc limit 100) order by `id`";
    $QUERY_SET_READED =
"UPDATE `chat_users` SET
    `lastmsg`=(SELECT `id` FROM `chat_log` ORDER BY `id` DESC LIMIT 1),
    `authtime`=%d
WHERE `id`=%d";

    if ($USER->lastmsg == 0)
        pushevent("erase;");
    
    $v1 = $db->query(sprintf($QUERY_GET_UNREADED, $USER->lastmsg));
    if ($v1 == false)
        return;
    
    while($v2 = $v1->fetch_object()) {
        switch ($v2->type) {
        case 0:
            pushchat($v2, $v2->text);
            break;
        case 1:
            pushchat($v2, nick($v2, 0), $v2->text);
            break;
        case 2: case 3: case 4:
            pushchat($v2, nick($v2, 0));
            break;
        case 5: case 6:
            pushchat($v2, nick($v2, 0), nick($v2, 1), $v2->text);
            break;
        case 7:
            if (($v2->u0 == $USER->id || $v2->u1 == $USER->id) || $USER->status > 3)
                pushchat($v2, $v2->u0 == $USER->id ? "Я" : $v2->n0, $v2->u1 == $USER->id ? "Я" : $v2->n1, $v2->text);
            break;
        case 8:
            if ($USER->lastmsg != 0)
                pushevent($v2->text);
            break;
        }
    }
        
    $db->query(sprintf($QUERY_SET_READED, time(), $USER->id));
}
