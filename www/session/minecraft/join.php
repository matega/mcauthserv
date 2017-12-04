<?php
include_once("../../functions.php");
$injson = file_get_contents('php://input');
file_put_contents("joinin.txt", $injson);
if($injson == "") $injson = <<<EOF
{
    "accessToken": "081a771e134e4b6bd73fec4eb7ce3bcd",
    "selectedProfile": "b4159253d21411e7900edb305f4b5dee",
    "serverId": "" 
}
EOF;
$inputarr = json_decode($injson, true);
$db = dbconnect();
$stmt = $db->prepare("UPDATE `user` INNER JOIN `profile` ON `user`.`id` = `profile`.`userid` SET `user`.`selectedprofile` = `profile`.`id`, `user`.`serverid` = ? WHERE `user`.`accesstoken` = ? AND `profile`.`uuid` = ?");
$accesstoken = htu($inputarr["accessToken"]);
$selectedprofile = htu($inputarr["selectedProfile"]);
$stmt->bind_param("sss", $inputarr["serverId"], $accesstoken, $selectedprofile);
$stmt->execute();
$stmt->store_result();
if(mysqli_stmt_affected_rows($stmt)) {
    http_response_code(204);
    file_put_contents("joinout.txt", "204");
} else {
    http_response_code(403);
    file_put_contents("joinout.txt", "403");
    print(json_encode(array("error"=>"ForbiddenOperationException","errorMessage"=>"Invalid token.")));
};
    $stmt->close();
?>
