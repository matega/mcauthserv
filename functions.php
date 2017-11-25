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
    return preg_match("/^[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}$/",$s);
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
        $userprops[] = array($pname=>$pvalue);
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
        $availableProfiles[] = array("id"=>$profileuuid, "name"=>$profilename);
        if($prid = $profileid) $selectedprofile = array("id"=>$profileuuid, "name"=>$profilename);
    }
    $stmt->close();
    if(!isset($selectedprofile) && count($availableProfiles)) $selectedprofile = $availableProfiles[0];
    return($availableProfiles);
}

?>
