<?php
function gen_uuid() {
 $uuid = array(
  'time_low'  => 0,
  'time_mid'  => 0,
  'time_hi'  => 0,
  'clock_seq_hi' => 0,
  'clock_seq_low' => 0,
  'node'   => array()
 );

 $uuid['time_low'] = mt_rand(0, 0xffff) + (mt_rand(0, 0xffff) << 16);
 $uuid['time_mid'] = mt_rand(0, 0xffff);
 $uuid['time_hi'] = (4 << 12) | (mt_rand(0, 0x1000));
 $uuid['clock_seq_hi'] = (1 << 7) | (mt_rand(0, 128));
 $uuid['clock_seq_low'] = mt_rand(0, 255);

 for ($i = 0; $i < 6; $i++) {
  $uuid['node'][$i] = mt_rand(0, 255);
 }

 $uuid = sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
  $uuid['time_low'],
  $uuid['time_mid'],
  $uuid['time_hi'],
  $uuid['clock_seq_hi'],
  $uuid['clock_seq_low'],
  $uuid['node'][0],
  $uuid['node'][1],
  $uuid['node'][2],
  $uuid['node'][3],
  $uuid['node'][4],
  $uuid['node'][5]
 );

 return $uuid;
}

function is_uuid($s) {
    return preg_match("/^[0-9a-fA-F]{8}-?([0-9a-fA-F]{4}-?){3}[0-9a-fA-F]{12}$/",$s);
}

function dbconnect() {
    return mysqli_connect("localhost","minecraft","minepass","minecraft",0,"/var/run/mysqld/mysqld.sock");
}

function get_userprops($db, $userid) {
    $userprops=array();
    $stmt = $db->prepare("SELECT `name`, `value` FROM `userprop` WHERE `userid` = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $stmt->bind_result($pname, $pvalue);
    while($stmt->fetch()) {
        $userprops[] = array("name"=>$pname, "value"=>$pvalue);
    }
    $stmt->close();
    return($userprops);
}

function get_availableprofiles($db, $userid, $profileid, &$selectedprofile) {
    $availableProfiles = array();
    $stmt = $db->prepare("SELECT `id`, `uuid`, `name` FROM `profile` WHERE `userid` = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $stmt->bind_result($prid, $profileuuid, $profilename);
    while($stmt->fetch()) {
        $availableProfiles[] = array("id"=>uth($profileuuid), "name"=>$profilename);
        if($prid = $profileid) $selectedprofile = array("id"=>uth($profileuuid), "name"=>$profilename);
    }
    $stmt->close();
    if(!isset($selectedprofile) && count($availableProfiles)) $selectedprofile = $availableProfiles[0];
    return($availableProfiles);
}

function htu($s) {
    return preg_replace("/^([0-9a-fA-F]{8})([0-9a-fA-F]{4})([0-9a-fA-F]{4})([0-9a-fA-F]{4})([0-9a-fA-F]{12})$/","\\1-\\2-\\3-\\4-\\5", uth($s));
}

function uth($s) {
    return preg_replace("/[^0-9a-fA-F]/","",$s);
}

function mkprofilearr($profileuuid, $profilename, $skin, $cape, $slim, $sigreq) {
    $textures = array(
        "timestamp" => round(microtime(true)*1000),
        "profileId" => uth($profileuuid),
        "profileName" => $profilename,
        "signatureRequired" => $sigreq,
        "textures" => array()
    );
    if(!is_null($skin)) {
        $skinarr = array(
            "url" => "https://sessionserver.mojang.com/session/minecraft/profile/$profileuuid?skin"
        );
        if($slim) $skinarr["metadata"] = array("model" => "slim");
        $textures["textures"]["SKIN"] = $skinarr;
    }
    if(!is_null($cape)) $textures["textures"]["CAPE"] = array(
            "url" => "https://sessionserver.mojang.com/session/minecraft/profile/$profileuuid?cape"
        );
    $texturesj = json_encode($textures, JSON_UNESCAPED_SLASHES);
    $texturesb = base64_encode($texturesj);
    $properties = array(
        "name" => "textures",
        "value" => $texturesb,
    );
    if($sigreq) {
        $succ = openssl_sign($texturesj, $sig, $glaceonprivkey, OPENSSL_ALGO_SHA1);
        if($succ) {
            $properties["signature"] = base64_encode($sig);
        }
    }
    $responsearr = array(
        "id" => uth($profileuuid),
        "name" => $profilename,
        "properties" => array($properties)
    );
    return $responsearr;
}

$glaceonprivkey = <<<EOF
-----BEGIN RSA PRIVATE KEY-----
MIIJKgIBAAKCAgEA8NFzqBxrP3ItlPjvrQu0k69rwxyR83X2ZbRAuFyNjlSRhmG6
Q9AiYpxOIc3oGGnDghexvL6SxhVBLKLjs8XHkX0BFvu4jk0TXpxqfM8/Ab0TMne7
1jTH7e+zgfPMT3+fDGVptNYIeR6Z6i1EalmB3R+l5p6qm8RpHPcandN76PWoUckj
3gJC5z+STT0CVImrN/8tvk6Z6fmjMJRQOo14nN6y70RqIRrbe6klMZ5NweFDnx/8
0CCE0qqmgzb6Eoygir0YWB1kZE2GRZbCv1/X0j5/Rs6AWuLaPdLKGKbrashpyRu8
xkHo7Tsnl6gejbUr8haGrvu/tQujy5rD5yA5aICfeVxt8/9yhe2EULRNe2vGMV16
LqWnHQCgPKeXKi0FwtXgHWSfE/izbdbSU64fyKontndpBq4nnTy0LArTeYSb448b
hT+emAE4jHnFWUekYk4BwSxVj4jozF6Z0betK/+c8fkh4I/qSOMiDE4rkw+UVQNT
ZhnJFYMDj41AIJ/28JMkJs9X62d8ixY3wf+tudE3hmKg2NS5zxxeJPw0pz/zfLYw
zB7APql45R/mhe2sn/uDQHG2StNZvVApkZPItbbCWZgVbYLas1N4Sba9dmFnUYKY
HhhPXMiy5qSjmU9gqdCvW9dUiNaiIY/C9oER+atKQ2//QqgvF8S5lKbH/XsCAwEA
AQKCAgEA5iiIzWc76UuK3RJ+n3jMgUnrSHMJ2xInCFUadhmfASU6YKEOkLlf47dX
xV8yMAMFZFSY/rd1N/QgcjTMQ4kHj7lVTU4PuZFYrdf4voikw1id/GILGFMhsfqi
KdWEs4ZZ0FMskDTvrDLfD71r4ejiASC6Qovqqy0w+QoCWk1B5AjFckU3DnxCzRkp
DsD8dSeOiNpOMdCVsdVxboCRnSSQ3fzKL3vHoxQ0KSAj+CRAVMijBHZh/djWmTGY
K1hj8U6OMYtAtRaCWIrvhZrtTlKfxsUMh/PdIQAnbWcE2ELQRSJ+WEM2TJW5tOIQ
mRWI/g7ljV6SIrNhKfuR9IpaKY/WzHtiEpvqKbCAv0TkPhh4dDgywNP0CUjtamh+
NlzcMd0VJZOVmsgG2XgRLzTa9mWNpfTOPwD4AZsxXfuR/7TurYGfkmreEBIxpv5n
ql/ZQuRILOeDdqlD9Ie4wn2IWW0e5wEH14Ohg3iJQP8q4oynI2yjKYw1cv+Zwx5e
cSod2PM9+k9gaOZEjvdftvneloTMK5e0FSaxHP3tTFynbSrWg1DpmfQyGe12izjO
FYSYDVgfndy3F04pVEJEWEuvhx3LExVk0/2nIWrVCJyu17Oz3xYM6rSRTvptrp0q
Zjrk0fTGthfzaPvmMtJKJnQKyyXrEPito9sDOLRq+2Ls2BeCnQECggEBAPoZ2Lew
HG8ydq1eBvAbOUQiczK/53hk3YzCBiGhednwOj5iX4NXxM5TYfSoLuq3yNEXoH3W
21dvNkmzXxCoZ9DYUxmc8YXIz4fXSaNnF742KuH+7kMxHxL9lz4e4+0aXBsTCk8S
FkSN3EFozxt+bxmIHnsutYOGAoHTsVzz8ln0V5ghFeHZ3w1QTWSyUHNHKRD7AJ+t
CYYbCvmuHN7zPWVisblSXeyIc/1JFTJovl79H1tB6QkhlEQdf3uACRXxZaDNE5Af
Rea2X8Cx5ginxHmYsjXlrMSiQM1mGrrAI8PAnfFfCXUaqNBYotZMctKjLlu0BnCQ
FcT4C3Qhelj+NVsCggEBAPZ/jdne7LlKijNNO/ck2ZJAfMMbmFvwWE+OPbyaIF8W
gOveZnmCKE9LoVUZ/pI/EKCxUJZS3781Eggb6MpOgKhhifmaptHgZ5wHGKkRdMyX
+1vsNIIBeqFzCZ1FwG9Mg2pB1r7vNiL2C1L9dDqIdExXKV+3v0N4cNPlPUCiOfEZ
zlYT8/KFkEBpwCGM9lU2tkVlSr+sZrKMerC9bhH1xuDwD1EcsWPVXxHmvdoL8ACt
4DbBRI29AImspJBohTdrYH9n4v+gn6HOBn/1zFk8+AQbMfi7QZ+L6vDqIuHImGCc
GDy49toH2qkIHVgiu2TW586nCtJhvPOMYz959dQ+MmECggEBAKaLs6+OzUCXQERu
yvv1hQsETeZLuN6JfKeRRPf8SeKBgKeuZqBWQC6NhDuiC6wuOsJSk4N5IpN08Zfe
pL25B3khtSTnSEao1NpG2TjBHCSEecYJN6zy9xh0WtT5SRrJZPB6m3DQQJfiR/we
yVwrs9wysumgzysIgH7n88JMiOSyO+qIjcPajBiXkxItVDmP957hJyct2Zu+Qm7G
LV+iZw4uzN72JKmwrFzM4BqzeaJTuR/SIlb8T3mUkyIwvtwn5l9QcWiWCrx6UgVF
8kNtRP8cWgBeM9C1o6WkCsS10ps2l3b2rJuY5zXm8Hj4a7YtCF/04BzxNi32Z9or
uQeuMOUCggEAJhYDRtyI2wBHZjHSyBbrfwtzu3myCtKR8ojZxSBTTB3gAZG1Z6TS
sZ3P6aRAjzwrR4jeGLvpUlPS22nkiA2lF18qwGRCzj7MA3GdP9Iwp8P71HIkksnD
ttpda+1xFEFQNMTDd3Difnhhu4rpwHmA7qKxA9cSOEyNsk/DLQ9jwHrqRKaElL5K
UYmFZCmxje0MMKC78e2jWIcYFeLO9PtypSKyWygByJkZPVzXjK83gANdl1g5TWUc
hikkrCNaIfv3Rvg4PKMGxytcGb1Su58N+yRwjivwscQeouaqSRSDZrn3jLpPedSo
1mEol4uWRLhnzKhGpcbwOPlCBHbFouuRQQKCAQEAgRfmWczrOyvM5MnYc8WlTmt/
z8IcGX9RHKR8hm3Xr7JBiXIP7uExhREbPI7o9wOrVpK0SkZn2m9Ai1/jdAe0MBoU
3QGxh7KivquykSC9MqoXnJhKhvsM8G5IoYVXo9Kl4Wqx1SNoYREjB6qPBjKFypdh
ictYbHrkZHygmFWYl+1J39S3ldg8DsUXaMyeep7khPFDEnrFYqYx39vlrjbzdttL
YcE0SPkvECLvQg0Jzj4KiiIIj2yrAH0EBeplzyoMGBi/fq3kLaBRNJCYfSzfgGel
T7Ko3jHeh9X378herF+sdSL/IWTsc46ayS6XJQsy8Owkm/ymZ10KU+SHS9eotA==
-----END RSA PRIVATE KEY-----
EOF
;

?>
