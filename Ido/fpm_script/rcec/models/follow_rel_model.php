<?php 
class follow_rel_model extends ModelBase
{
	public function b_my_fans($uid, $fans_id)
	{
		$sql = "SELECT * FROM cms_manager.follow_user_record WHERE uid=$fans_id AND fid=$uid AND type=1";
		$db_cms = $this->getDbMain();
		
		$rows = $db_cms->query($sql);
		
		if (!empty($rows) && $rows->num_rows > 0) {
			return true;
		} else {
			return false;
		}
	}
}
?>