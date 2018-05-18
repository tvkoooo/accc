<?php
define('RCEC_ROOT', dirname(__FILE__));
define('MAINTAIN', false);
require RCEC_ROOT . "/../GlobalConfig.php";
//require RCEC_ROOT . "/../util/Utils.php";


function __autoload ($class)
{
    require RCEC_ROOT . '/models/' . $class . '.php';
}

function call ($jsonParams, $jsonData)
{
    Logger::fileLog("$jsonParams => $jsonData");
    $timeStart = microtime(true);
    $paramList = json_decode($jsonParams, true);

    $serverData = array();
    if (! empty($paramList['server_data']) && is_array($paramList['server_data'])) 
    {
        $serverData = $paramList['server_data'];
    }
	
    $userData = array();
    if (! empty($paramList['user_data']) && is_array($paramList['user_data'])) 
    {
        $userData = $paramList['user_data'];
    }
	
    $params = array_merge($serverData, $userData);
    $command = $params['cmd'];
    $success = true;
    $maintain = MAINTAIN ? true : false;
    if (isset($params['sid']) && $params['sid'] == '25497647') {
        $maintain = false;
    }
	
    if ($maintain) {
        $result = array(
            array(
                'broadcast' => 0,
                'data' => array(
                    'cmd' => 'RServerStatus',
                    'errorMessage' => ''
                )
            )
        );
    }
    else 
    {
        GlobalConfig::assign_server_id($serverData);
		//$sn = ProtocolTracer::getInstance()->getNextSeqNum();
		//ProtocolTracer::getInstance()->add($sn, $command, $userData, microtime(true));
        $result = FrontControl::execCommand($command, $params);
		//ProtocolTracer::getInstance()->done($sn, microtime(true));
		
    }
    if ($result === - 1 || empty($result)) {
        $success = false;
    }

    $result = cback_api::deal_cback($params['sid'], $result);

    if (defined('JSON_NUMERIC_CHECK')) 
    {
        $packet_real = array
        (
            'success' => $success,
            'result' => $result,
            'server_data' => $serverData,
            // 'user_data' => $userData,
        );
        $return = json_encode($packet_real);
    } 
    else 
    {
        $packet_real = array
        (
            'success' => $success,
            'result' => $result,
            'server_data' => $serverData,
        );
        $return = json_encode($packet_real);
    }

    $timeCost = round((microtime(true) - $timeStart) * 1000, 2);
    // 不再往缓存写
//    if ($success) {
//        Logger::sendDataCenterCommandLog($command, $timeCost);
//    }

    ServiceFactory::cleanService();
    //if ($command == 'PHandShake') {
        Logger::fileLog("$jsonParams \n => $return \n");
    //}

    return $return;
}

?>