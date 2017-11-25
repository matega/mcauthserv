<?php
include_once("common.php");
    $injson = file_get_contents('php://input');
    file_put_contents("authin", $injson);
    $inputarr = json_decode($injson, true);
    $db = dbconnect();
    $uf = $db::prepare("SELECT `id`,`pass`,`uuid`,`selectedprofile` FROM `user` WHERE `name` = ?;")::bind_param('s',$inputarr["username"])::bind_result($userid,$userpass,$accuuid,$profileid)::execute()::fetch();
    if(!$uf || !password_verify($inputarr["password"],$userpass)) {
        http_response_code(403);
        print(json_encode(array("error"=>"ForbiddenOperationException","errorMessage"=>"Invalid credentials. Invalid username or password.")));
        die();
    }
    $responsearr = array(
        "accessToken"=> gen_uuid(),
        "clientToken"=> $inputarr["clientToken"],
    );
    if($inputarr["agent"]) $responsearr["availableProfiles"] = array(
        array(
                "id" => "839b7906-d00f-448b-fa00-e5886a2b4028",
                "name" => "matega"
        )
    );
    if($inputarr["requestUser"]) $responsearr["user"] = array(
        "id" => "839b7906-d00f-448b-fa00-e5886a2b4028",
        "properties" => array(
            array(
                "name" => "preferredLanguage",
                "value" => "en"
            )
        )
    );
    file_put_contents("authout", json_encode($responsearr));
    print(json_encode($responsearr));
?>
