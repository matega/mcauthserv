<?php
include_once("../../functions.php");
file_put_contents("hjin.txt",$_SERVER["QUERY_STRING"]);
$db = dbconnect();
$stmt = $db->prepare("SELECT `profile`.`uuid`, `profile`.`name`, `profile`.`skin`, `profile`.`cape` FROM `profile` INNER JOIN `user` ON `user`.`id` = `profile`.`userid` WHERE `user`.`serverid` = ? AND `profile`.`name` = ?");
$stmt->bind_param("ss",$_GET["serverId"], $_GET["username"]);
$stmt->execute();
$stmt->bind_result($profileuuid, $profilename, $skin, $cape);
if($stmt->fetch()) {
    $responsearr = array(
        "id" => uth($profileuuid),
        "name" => $profilename,
        "properties" => array()
    );
    file_put_contents("hjout.txt", json_encode($responsearr));
    print(json_encode($responsearr));
} else {
    http_response_code(403);
}
