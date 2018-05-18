<?php

class callback_dispatch_model extends ModelBase
{
	const redis_key_callback_list = "queue:list:sess:notify";

	public function dispatch_channel($data, $sid)
	{
		$model = new cback_channel_model();
		$model->broadcast(intval($sid), $data);

		LogApi::logProcess("callback_dispatch_model dispatch_channel. sid:$sid data: " . json_encode($data));
	}
}
?>