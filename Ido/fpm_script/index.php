<?php
define("ROOT_PATH", dirname(__FILE__));
define("LIB_PATH", ROOT_PATH . "/lib");

$method = $_POST["method"];
$params = $_POST["params"];
$data = $_POST["data"];

$file = str_replace(".", "/", $method) . ".php";
include $file;

//LogApi::logProcess('index begin:  pid='.posix_getpid().'method='.$method.',params='.$params.',data='.$data);

if (function_exists("call")) {
    ob_start();
    $result = call($params, $data);
    ob_clean();
    if ($result === null) {
        $result = array();
        $result["success"] = false;
        $result["error"] = 500;
        echo json_encode($result);
    } else {
        echo $result;
    }
} else {
    $result = array();
    $result["success"] = false;
    $result["error"] = 500;
    echo json_encode($result);
}

//LogApi::logProcess('index end:  pid='.posix_getpid());

?>
