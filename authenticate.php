<?php
include_once("functions.php");
$injson = file_get_contents('php://input');
file_put_contents("authin", $injson);
if($injson == "") $injson = <<<EOF
{
"agent": {
    "name": "Minecraft",
    "version": 1
},
"username": "matega",
"password": "pass",
"clientToken": "00000000-0000-0000-0000-000000000000",
"requestUser": true
}
EOF;
$inputarr = json_decode($injson, true);
$db = dbconnect();
$stmt = $db->prepare("SELECT `id`,`pass`,`uuid`,`selectedprofile` FROM `user` WHERE `name` = ?");
$stmt->bind_param('s', $inputarr["username"]);
$stmt->execute();
$stmt->bind_result($userid, $userpass, $accuuid, $profileid);
$uf = $stmt->fetch();
$stmt->close();
if(!$uf || !password_verify($inputarr["password"],$userpass)) {
    http_response_code(403);
    print(json_encode(array("error"=>"ForbiddenOperationException","errorMessage"=>"Invalid credentials. Invalid username or password.")));
    die();
}
$accesstoken = gen_uuid();
$clienttoken = is_uuid($inputarr["clientToken"])?$inputarr["clientToken"]:gen_uuid();
$stmt = $db->prepare("UPDATE `user` SET `clienttoken` = ?, `accesstoken` = ? WHERE `id` = ?");
$stmt->bind_param('ssi', $clienttoken, $accesstoken, $userid);
$stmt->execute();
$stmt->close();

if($inputarr["agent"]["name"] == "Minecraft") {
    $selectedprofile = null;
    $availableProfiles = get_availableprofiles($db, $userid, $profileid, $selectedprofile);
}
if($inputarr["requestUser"]) {
    $userobj = array("id"=>htu($accuuid), "properties"=>get_userprops($db, $userid));
}
$responsearr = array(
    "accessToken" => uth($accesstoken),
    "clientToken" => $clienttoken
);
if($inputarr["agent"]["name"] == "Minecraft") {
    $responsearr["availableProfiles"] = $availableProfiles;
    if($selectedprofile) $responsearr["selectedProfile"] = $selectedprofile;
}
if($inputarr["requestUser"]) {
    $responsearr["user"] = $userobj;
}
file_put_contents("authout", json_encode($responsearr));
print(json_encode($responsearr));
?>
