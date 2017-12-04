<?php
include_once("functions.php");
$injson = file_get_contents('php://input');
file_put_contents("refreshin", $injson);
if($injson == "") $injson = <<<EOF
{
    "accessToken": "081a771e-134e-4b6b-d73f-ec4eb7ce3bcd",
    "clientToken": "978a6b5d-6e70-41af-99a2-1b74d9532375"
}
EOF;
$inputarr = json_decode($injson, true);
$db = dbconnect();
$clienttoken = array_key_exists("clientToken", $inputarr)?$inputarr["clientToken"]:"";
$skipct = !array_key_exists("clientToken", $inputarr);
$stmt = $db->prepare("SELECT 1 FROM `user` WHERE `accesstoken` = ? AND ((`clienttoken` = ?) OR ?) AND `accesstoken` IS NOT NULL");
$stmt->bind_param("ssi", $inputarr["accessToken"], $clienttoken, $skipct);
$stmt->execute();
$stmt->bind_result($a);
$stmt->fetch();
http_response_code($a?204:403);
if(!$a) {
    print(json_encode(array("error"=>"ForbiddenOperationException","errorMessage"=>"Invalid token.")));
}
