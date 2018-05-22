<?php

class BroadcastOnline
{

    public function callFansByIm($users, $params, $messageType = 'follow')
    {
        $data = array();
        $data["service"] = "ImForwarder";
        $data["type"] = 7;
        $data["sender"] = $params['uid'];
        $data["users"] = $users;
        $nick = base64_encode($params['nick']);
        if ($messageType == 'follow') {
            $text = base64_encode(
                "[size=10]您關注的表演者[color=#8C39FE][b]" . $params['nick'] . "[/b][/color]上麥啦！他邀請您一起來互動!\n" .
                "[link=followUser#" . $params['sid'] . "#" . $params['uid'] . "]馬上進入表演現場[/link][/size]\n\n" .
                "[size=10][color=#808080]本訊息由RC系統自動發送，不用回覆哦。如您覺得有任何不便，請點擊這裡[/color] " .
                "[link=manageAttention#http://www.showoo.cc/rcec/index.php?cmd=showPersonalHome&uid=" .
                $params['uid'] . "&param=]取消關注[/link][/size]");
        } elseif ($messageType == 'guard') {
            $text = base64_encode(
                "[size=10]您守護的表演者[color=#8C39FE][b]" . $params['nick'] . "[/b][/color]上麥啦！\n" .
                "[link=followUser#" . $params['sid'] . "#" . $params['uid'] .
                "]馬上進入表演現場[/link][/size]\n\n" .
                "[size=10][color=#808080]本訊息由RC系統自動發送，不用回覆哦。[/color][/size]");
        } else {
            $text = '';
        }
        $data["message"] = base64_encode(
            "<msg><type>1</type><nick>$nick</nick><title>粉絲通知</title><text>" . $text . "</text><close>1200</close><sid>"
            . $params['sid'] . "</sid><uid>" . $params['uid'] . "</uid></msg>");
        $curlPost = "data=" . base64_encode(json_encode($data, JSON_NUMERIC_CHECK));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://211.72.192.14:10000');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1); // post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $result = curl_exec($ch);
        curl_close($ch);
        if (empty($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function callFans($users, $params)
    {
        $sendstr = $this->getContent($params);
        return $this->broadcastOnlines($users, base64_encode($sendstr), gethostbyname('broadcast.showoo.cc'));
    }

    protected function getContent($params = array())
    {
        $str_message = '<?xml version="1.0" encoding="utf-8"?>';
        $str_message .= '<controlbody>';
        $str_message .= '<notify version="7.0">';
        $str_message .= '<id>-1</id>';
        $str_message .= '<caption >直播通知</caption>';
        $str_message .= '<style value="300">2</style>';
        $str_message .= '<text>';
        $str_message .= '<![CDATA[';
        $str_message .= '<div style="flow:horizontal"><div style="width:96px"><img src="http://img.showoo.cc/getimg.php?img=' .
            $params['uid'] .
            '" style="width:80px; height:80px; background:#FFF; padding:1px; border:1px solid #C3C2BE; border-radius:3px; outline:2px glow #C3C2BE" /></div>"
            ."<div style="width:204px; height:84px; vertical-align:middle; font-size:13px">您關注的主播<span style="color:#F00">' .
            $params['sender'] .
            '</span>開直播啦！</div></div><div style="text-align:center; padding-top:15px"><a class="btn" href="rc://enterChannel/?sid=' .
            $params['sid'] . '&uid=' . $params['uid'] . '&from=popwin">進入表演現場</a></div>';
        $str_message .= ']]>';
        $str_message .= '</text>';
        $str_message .= '</notify>';
        $str_message .= '</controlbody>';
        return $str_message;
    }

    protected function getRedisConfig()
    {
        $redis_array = array();
        $redis_array['redis'] = array();
        $redis_array['redis']['ip'] = "redis.report.raidcall.com";
        $redis_array['redis']['port'] = "6379";
        return $redis_array;
    }

    protected function getRedis()
    {
        try {
            $redis = new Redis();
            $config = $this->getRedisConfig();
            if ($redis->connect($config['redis']['ip'], $config['redis']['port'], 1)) {
                // $redis->auth("rc#report#pwd");
                return $redis;
            }
        } catch (Exception $e) {
        }
        return false;
    }

    protected function broadcastOnlines($users, $infos, $ip)
    {
        $path = RCEC_ROOT . "/.pass_param";
        $u_filename = $path . "/user." . time() . "." . mt_rand() . ".conf";
        $i_filename = $path . "/info." . time() . "." . mt_rand() . ".conf";
        $u_file = fopen($u_filename, "a+");
        $i_file = fopen($i_filename, "a+");
        if (!$u_file || !$i_file) {
            fclose($u_file);
            fclose($i_file);
            return $users;
        }
        // base64 infos => i_file
        $infos = base64_encode($infos);
        fwrite($i_file, $infos);
        $broadcast_app = "";
        if (is_array($users)) {
            $redis = $this->getRedis();
            if ($redis) {
                $onlines = $redis->hMGet("user#online", $users);
                if ($onlines) {
                    foreach ($onlines as $uid => $server) {
                        if ($server) {
                            fwrite($u_file, "$uid $server \n");
                        }
                    }
                    $broadcast_app = RCEC_ROOT . "/broadcast $ip userver $i_filename $u_filename";
                }
            }
        } else {
            // 發全部人
            // if ($users == "allonlines") {
            // $broadcast_app = "./broadcast $ip alluser $i_filename";
            // }
        }
        fclose($u_file);
        fclose($i_file);
        $ret = "";
        if ($broadcast_app) {
            $ret = exec($broadcast_app);
        }
        if ($ret && strpos($ret, "su") !== false) {
            return true;
        } else {
            return false;
        }
    }
}
