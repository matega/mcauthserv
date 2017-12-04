<?php
include_once("functions.php");
$injson = file_get_contents('php://input');
file_put_contents("refreshin", $injson);
if($injson == "") $injson = <<<EOF
{
    "accessToken": "7c29059d-8238-42aa-de6b-92750c2e5a37",
    "clientToken": "00000000-0000-0000-0000-000000000000",
    "selectedProfile": {
        "id": "profile identifier",
        "name": "player name"
    },
    "requestUser": true
}
EOF;
$inputarr = json_decode($injson, true);
$db = dbconnect();
$stmt = $db->prepare("SELECT `id`, `uuid`, `selectedprofile` FROM `user` WHERE `accesstoken` = ? AND `clienttoken` = ? AND `accesstoken` IS NOT NULL");
$accesstoken = htu($inputarr["accessToken"]);
$stmt->bind_param("ss", $accesstoken, $inputarr["clientToken"]);
$stmt->execute();
$stmt->bind_result($userid, $accuuid, $profileid);
if(!$stmt->fetch()) {
    http_response_code(403);
    print(json_encode(array("error"=>"ForbiddenOperationException","errorMessage"=>"Invalid token.")));
    die();
}
$stmt->close();
$clienttoken = $inputarr["clientToken"];
$accesstoken = gen_uuid();
$stmt = $db->prepare("UPDATE `user` SET `accesstoken` = ? WHERE `id` = ?");
$stmt->bind_param('si', $accesstoken, $userid);
$stmt->execute();
$stmt->close();
$responsearr = array(
    "accessToken" => htu($accesstoken),
    "clientToken" => $clienttoken
);
$selectedprofile = null;
get_availableprofiles($db, $userid, $profileid, $selectedprofile);
if($inputarr["requestUser"]) {
    $userobj = array("id"=>uth($accuuid), "properties"=>get_userprops($db, $userid));
}
$responsearr = array(
    "accessToken" => $accesstoken,
    "clientToken" => $clienttoken
);
if($selectedprofile) $responsearr["selectedProfile"] = $selectedprofile;
if($inputarr["requestUser"]) {
    $responsearr["user"] = $userobj;
}
print(json_encode($responsearr));
?>
