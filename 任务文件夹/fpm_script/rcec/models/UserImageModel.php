<?php

class UserImageModel extends ModelBase
{

    const SIZE_RAW = 0;

    const SIZE_STANDARD = 1;

    const SIZE_SMALL = 2;
    

    public function __construct ()
    {
        parent::__construct();
    }

    public function getImagesByUid ($uid)
    {
        $key = 'user_image:' . $uid;
        $query = "select * from user_image where uid = $uid";
        return $this->read($key, $query);
    }

    public function getDefaultImage ($uid, $size = 2)
    {
        $userAttrModel = new UserAttributeModel();
        $userAttr = $userAttrModel->getAttrByUid($uid);
        if (! empty($userAttr['default_image'])) {
            // 表示用戶有頭像
            $settingsModel = new SettingsModel();
            $onmicImageUrl = $settingsModel->getValue('ONMIC_IMAGE_URL');
            $images = $this->getImagesByUid($uid);
            if (count($images) > 0) {
                foreach ($images as $image) {
                    if ($image['parent'] == $userAttr['default_image'] && $image['size'] == $size) {
                        return $onmicImageUrl . $image['image_id'] . '&t=' . $image['last_modified'];
                    }
                }
            }
        }
        return '';
    }
}
?>