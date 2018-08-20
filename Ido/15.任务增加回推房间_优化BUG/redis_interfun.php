<?
require_once dirname(__FILE__)."/../../bases/GlobalConfig.php";

function getRedisConfig() 
{
    $redis_array = array();
    $db_config = GlobalConfig::$__static_config[GlobalConfig::$SERVER_ID]['db'];
    $db_config_redis = $db_config['redis'];
    $db_config_mysql_master = $db_config_redis['master'];
    $db_config_mysql_slave = $db_config_redis['slave'];
    $db_config_mysql_cback = $db_config_redis['cback'];
    
    $redis_array['redis'] = array();
    $redis_array['redis']['ip']	= $db_config_mysql_master[0];
    $redis_array['redis']['port'] = $db_config_mysql_master[1];
    $redis_array['redis']['passwd']	= $db_config_mysql_master[2];
    //$redis_array['redis']['timeout'] = 0.3;
    $redis_array['redis']['timeout'] = 3;//<2018-07-23 lj>
    
    $redis_array['redis_slave']	= array();
    $redis_array['redis_slave']['ip'] = $db_config_mysql_slave[0];
    $redis_array['redis_slave']['port']	= $db_config_mysql_slave[1];
    $redis_array['redis_slave']['passwd'] = $db_config_mysql_slave[2];
    //$redis_array['redis_slave']['timeout'] = 0.1;
    $redis_array['redis_slave']['timeout'] = 3;//<2018-07-23 lj>
    
    //<2018-07-23 lj>
    $redis_array['redis_cback']	= array();
    $redis_array['redis_cback']['ip'] = $db_config_mysql_cback[0];
    $redis_array['redis_cback']['port']	= $db_config_mysql_cback[1];
    $redis_array['redis_cback']['passwd'] = $db_config_mysql_cback[2];
    $redis_array['redis_cback']['timeout'] = 3;
    
    
    return $redis_array;
    
// 	$redis_array = array();

// 	$redis_array['redis'] = array();
// 	//$redis_array['redis']['ip']	= "123.56.103.80";
// 	$redis_array['redis']['ip']	= "redislocal.prsoft";
// 	$redis_array['redis']['port'] = "6379";
// 	$redis_array['redis']['passwd']	= "xcRed.,0505";
// 	$redis_array['redis']['timeout'] = 0.3;  

// 	$redis_array['redis_slave']	= array();
// 	//$redis_array['redis_slave']['ip'] = "123.56.103.80";
// 	$redis_array['redis_slave']['ip'] = "redislocal.prsoft";
// 	$redis_array['redis_slave']['port']	= "6379";
// 	$redis_array['redis_slave']['passwd'] = "xcRed.,0505";
// 	$redis_array['redis_slave']['timeout'] = 0.1;  

// 	return $redis_array;
}

function getRedis() {
//return false;
	try {
		$redis = new Redis();
		//echo "********************1!";
		$config = getRedisConfig();
		//echo "ip:".$config['redis']['ip']." port:".$config['redis']['port'];
		if ($redis->connect($config['redis']['ip'], $config['redis']['port'],
					$config['redis']['timeout']))
		{
			$redis->auth($config['redis']['passwd']);
		} else if ($redis->connect($config['redis_slave']['ip'],$config['redis_slave']['port'],
				$config['redis_slave']['timeout'])) {
			$redis->auth($config['redis_slave']['passwd']);
		} else {
			return false;
		}
		if ($redis->ping()) {
		    //echo "redis success!";
			return $redis;
		} else {
		    echo "ip:".$config['redis']['ip']." port:".$config['redis']['port'];
			echo "redis error!";
		}
	} catch (RedisException $e) {
	    echo "ip:".$config['redis']['ip']." port:".$config['redis']['port'];
		echo "redis error!".$e->getMessage()."\n";
	}
	return false;
}

//<2018-07-23 lj>
function getCbackRedis() {
    //return false;
    try {
        $redis = new Redis();        
        $config = getRedisConfig();        
        if ($redis->connect($config['redis_cback']['ip'], $config['redis_cback']['port'],
            $config['redis_cback']['timeout']))
        {
            $redis->auth($config['redis_cback']['passwd']);
        }else {
			return false;
		}
        if ($redis->ping()) {
            
            return $redis;
        } else {
            echo "ip:".$config['redis_cback']['ip']." port:".$config['redis_cback']['port'];
            echo "redis_cback error!";
        }
    } catch (RedisException $e) {
        echo "ip:".$config['redis_cback']['ip']." port:".$config['redis_cback']['port'];
        echo "redis_cback error!".$e->getMessage()."\n";
    }
    return false;
}











/*
function dryRun() {
	$redis = getRedis();
	if ($redis) {
		echo $redis->dbsize() ."\n";
		echo $redis->ping() ."\n";
	}
}

dryRun();
*/

?>
