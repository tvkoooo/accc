<?php

/**
 * 分布式服务单例工厂
 */
class ServiceFactory
{

    public static $_instance = array();

    public static function getService ($name, $index)
    {
        $config = Config::getConfig();
		
        $dataCenterIp = $config['data_center']['ip'];
        $key = $name . '_' . $index;
		
        if (! empty(self::$_instance[$key])) {
            return self::$_instance[$key];
        }
        if (! isset($config[$name][$index])) {
            trigger_error("unknown key: $name, $index");
        }
        $conf = $config[$name][$index];
        switch ($name) {
            case 'mysql':
                $timeStart = microtime(true);
                $port = '3306';
                if (! empty($conf[4])) {
                    $port = $conf[4];
                }
                $service = new MysqlServer($conf[0], $conf[1], $conf[2], $conf[3], $port);
                $timeCost = round((microtime(true) - $timeStart) * 1000, 2);
                if ($service->connect_error) {
                    $error = 'fail to connect mysql ' . $conf[0] . ':' . $port;
                    Logger::fileLog($error, $timeCost, 'error');
                    echo json_encode(
                            array(
                                'success' => false,
                                'result' => $error
                            ));
                    exit();
                }
                $service->query('SET NAMES UTF8MB4');
                // 不再写队列
                //Logger::sendServerConnectLog($dataCenterIp, $conf[0] . ':' . $port, 'mysql', $timeCost);
                break;
            case 'redis':
                $timeStart = microtime(true);
                $service = new RedisServer();
                $connect = $service->connect($conf[0], $conf[1], 4); // timeout为4秒
                $a = $service->auth($conf[2]);
                $timeCost = round((microtime(true) - $timeStart) * 1000, 2);
                if (! $connect) {
                    $error = 'fail to connect redis ' . $conf[0] . ':' . $conf[1];
                    Logger::fileLog($error, $timeCost, 'error');
                    echo json_encode(
                            array(
                                'success' => false,
                                'result' => $error
                            ));
                    exit();
                }
				
				if (! $a) {
                    $error = 'redis auth failed with ' . $conf[2];
                    Logger::fileLog($error, $timeCost, 'error');
                    echo json_encode(
                            array(
                                'success' => false,
                                'result' => $error
                            ));
                    exit();
                }
                
                // 不再写队列
				//Logger::sendServerConnectLog($dataCenterIp, $conf[0] . ':' . $conf[1], 'redis', $timeCost);
                break;
            default:
                break;
        }
        self::$_instance[$key] = $service;
        return $service;
    }

    public static function cleanService ()
    {
        if (! empty(self::$_instance)) {
            foreach (self::$_instance as $key => $service) {
                $service->close();
            }
            self::$_instance = array();
        }
    }
}
?>