<?php
include_once("functions.php");
$injson = file_get_contents('php://input');
file_put_contents("authin", $injson);
if($injson == "") $injson = <<<EOF
{
"username": "matega",
"password": "pass"
}
EOF;
$inputarr = json_decode($injson, true);
$db = dbconnect();
$stmt = $db->prepare("SELECT `id`,`pass` FROM `user` WHERE `name` = ?");
$stmt->bind_param('s', $inputarr["username"]);
$stmt->execute();
$stmt->bind_result($userid, $userpass);
$uf = $stmt->fetch();
$stmt->close();
if(!$uf || !password_verify($inputarr["password"],$userpass)) {
    http_response_code(403);
    print(json_encode(array("error"=>"ForbiddenOperationException","errorMessage"=>"Invalid credentials. Invalid username or password.")));
    die();
}
$stmt = $db->prepare("UPDATE `user` SET `accesstoken` = NULL, `clienttoken` = NULL WHERE `id` = ?");
$stmt->bind_param('i', $userid);
$stmt->execute();
$stmt->close();
http_response_code(204);
?>
