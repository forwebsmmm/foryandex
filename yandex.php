<?php
include_once ('Z:\home\testphp.ru\www\foryandex\yaru.php');

define (CLIENT_ID, 'b32efe8f5f8e4b72bd8a43d2f94cd357');
define (CLIENT_SECRET, '3365cea0703240ae83a3a4391073c221');
define (URI, '234701151');

if (!isset($_GET["code"])) {
	Header ("Location: https://oauth.yandex.ru/authorize?response_type=code&client_id=".CLIENT_ID);
	die();
}

$object = new yaruapi(CLIENT_ID, CLIENT_SECRET, URI);

//1текст и 2номер поста
$object->comment("add comment", 1);
//текст статуса
$object->status("add status");
//1титул и 2текст
$object->post("post", "add post");
?>
