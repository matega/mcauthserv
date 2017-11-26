<?php
include_once("functions.php");
$injson = file_get_contents('php://input');
file_put_contents("refreshin", $injson);
if($injson == "") $injson = <<<EOF
{
    "accessToken": "081a771e134e4b6bd73fec4eb7ce3bcd",
    "clientToken": "978a6b5d-6e70-41af-99a2-1b74d9532375"
}
EOF;
$inputarr = json_decode($injson, true);
$db = dbconnect();
$clienttoken = array_key_exists("clientToken", $inputarr)?$inputarr["clientToken"]:"";
$skipct = !array_key_exists("clientToken", $inputarr);
$stmt = $db->prepare("SELECT `id` FROM `user` WHERE `accesstoken` = ? AND ((`clienttoken` = ?) OR ?)");
$accesstoken = htu($inputarr["accessToken"]);
$stmt->bind_param("ssi", $accesstoken, $clienttoken, $skipct);
$stmt->execute();
$stmt->bind_result($userid);
$stmt->fetch();
http_response_code($userid?204:403);
if(!$a) {
    print(json_encode(array("error"=>"ForbiddenOperationException","errorMessage"=>"Invalid token.")));
    die();
}
$stmt = $db->prepare("UPDATE `user` SET `accesstoken` = NULL, `clienttoken` = NULL WHERE `id` = ?");
$stmt->bind_param('i', $userid);
$stmt->execute();
$stmt->close();
http_response_code(204);
?>
