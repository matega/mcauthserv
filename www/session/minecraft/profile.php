<?php
include_once("../../functions.php");
file_put_contents("profilein.txt",$_SERVER["QUERY_STRING"]);
$uuid = htu($_SERVER["PATH_INFO"]);
$db = dbconnect();
$stmt = $db->prepare("SELECT `uuid`, `name`, `skin`, `cape`, `slim` FROM `profile` WHERE `profile`.`uuid` = ?");
$stmt->bind_param("s",$uuid);
$stmt->execute();
$stmt->bind_result($profileuuid, $profilename, $skin, $cape, $slim);
$profileuuid = htu($profileuuid);
$sigreq = ($_GET["unsigned"] == "false");
if($stmt->fetch()) {
    switch($_SERVER["QUERY_STRING"]) {
        case "skin":
            print($skin);
            break;
        case "cape":
            print($cape);
            break;
        default:
            print(json_encode(mkprofilearr($profileuuid, $profilename, $skin, $cape, $slim, $sigreq)));
    }
} else {
    http_response_code(403);
}
?>
