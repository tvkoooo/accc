<?php

class BroadType
{
	//单一用户接收
	const USER = 0;
	//用户所在直播间接收
	const CHANNEL = 1;
	const ROOM = 2;
	//全区接收（单一session服务器上的房间全部接收？？？）
	const SESSION = 3;
}
