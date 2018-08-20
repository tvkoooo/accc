<?php

class task_pack_u64_little_endian
{
	public static function pack($u64)
	{
		$flag1 = 0x00000000FFFFFFFF;
		$flag2 = 0xFFFFFFFF00000000;

		return pack("VV", $u64 & $flag1, ($u64 & $flag2) >> 32);
	}
} 

class task_packet_head_model
{
	public $mid;	//u32
	public $pid;	//u32
	public $sid;	//u64
	public $uid;	//u64

	public function pack()
	{
		return pack("VV", $this->mid, $this->pid) . task_pack_u64_little_endian::pack($this->sid) . task_pack_u64_little_endian::pack($this->uid);
	}
}

class task_packet_model
{
	public static function new_multicast($sid, $data, $uids)
	{
		return new task_packet_multicast_model($sid, $data, $uids);
	}

	public static function new_unicast($sid, $uid, $data)
	{
		return new task_packet_unicast_model($sid, $uid, $data);
	}

	public static function new_broadcast($sid, $data)
	{
		return new task_packet_broadcast_model($sid, $data);
	}
}


class task_packet_base
{
	public $byte_header;
	public $byte_buffer;

	public function pack()
	{
		$len_head = strlen($this->byte_header);	// u16
		$len_body = strlen($this->byte_buffer);	// u16

		$len_total = $len_head + $len_body + 4; // u16

		return pack("vv", $len_total, $len_head) . $this->byte_header . $this->byte_buffer;
	}
}

class task_packet_multicast_model extends task_packet_base
{
	public $head;
	public $data;
	public $uids;

	public function __construct($sid, $data, $uids)
	{
		$this->head = new task_packet_head_model();
		$this->head->mid = 0;
		$this->head->pid = 0xFFFFFFFF;
		$this->head->sid = intval($sid);
		$this->head->uid = 0;

		$this->data = $data;
		$this->uids = $uids;
	}

	public function pack()
	{
		// header
		$this->byte_header = $this->head->pack();

		// data
		$this->byte_buffer = pack("Va*", strlen($this->data), $this->data);

		// uids
		$this->byte_buffer .= pack("V", count($this->uids));

		foreach ($this->uids as $uid) {
			$this->byte_buffer .= task_pack_u64_little_endian::pack(intval($uid));
		}

		return parent::pack();
	}
}

class task_packet_unicast_model extends task_packet_base
{
	public $head;
	public $data;

	public function __construct($sid, $uid, $data)
	{
		$this->head = new task_packet_head_model();
		$this->head->mid = 0;
		$this->head->pid = 0;
		$this->head->sid = intval($sid);
		$this->head->uid = intval($uid);

		$this->data = $data;
	}

	public function pack()
	{
		// header
		$this->byte_header = $this->head->pack();

		// buffer;
		$this->byte_buffer = pack("a*", $this->data);

		return parent::pack();
	}
}

class task_packet_broadcast_model extends task_packet_base
{
	public $head;
	public $data;

	public function __construct($sid, $data)
	{
		$this->head = new task_packet_head_model();
		$this->head->mid = 0;
		$this->head->pid = 0;
		$this->head->sid = intval($sid);
		$this->head->uid = -1;

		$this->data = $data;
	}

	public function pack()
	{
		// header
		$this->byte_header = $this->head->pack();

		// buffer;
		$this->byte_buffer = pack("a*", $this->data);

		return parent::pack();
	}
}


?>