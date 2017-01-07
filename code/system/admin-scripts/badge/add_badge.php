<?php

class SJB_Admin_Badge_AddBadge extends SJB_Function
{
	public function isAccessible()
	{
         return (SJB_Settings::getSettingByName('gradlead_enable_application'));
	}

	public function execute()
	{
		$tp = SJB_System::getTemplateProcessor();
		$errors = array();
		$badgeErrors = array();

		$userGroup = SJB_UserGroupManager::getUserGroupInfoBySID(SJB_Request::getVar('user_group_sid'));

		$badge = new SJB_Badge($_REQUEST);
		$pages = $badge->getBadgePages();
		$addBadgeForm = new SJB_Form($badge);
		$addBadgeForm->registerTags($tp);
		$form_submitted = SJB_Request::getVar('action', '') == 'save';
		if ($form_submitted) {
			$badgeErrors = $badge->isValid($badge);
		}

		if ($form_submitted && $addBadgeForm->isDataValid($errors) && !$badgeErrors) {
            $file_id_current = 'badge_' . md5(microtime());
            $upload_manager = new SJB_UploadFileManager();
            $upload_manager->setFileGroup('files');
            $upload_manager->setUploadedFileID($file_id_current);
            $file_name = $upload_manager->uploadFile('file_tmp');
            $id_file = $upload_manager->fileId;

            $mimeType = isset($_FILES['file_tmp']['type']) ? $_FILES['file_tmp']['type'] : '';

            if (isset($_FILES['file_tmp']['error'])) {
                switch ($_FILES['file_tmp']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $errors['FILE_SIZE'] = 'File size shouldn\'t be larger than 5 MB.';
                }
            }

            if (empty($errors) && isset($_FILES['file_tmp']['size']) && $file_name != '' && $_FILES['file_tmp']['size'] == 0) {
                $errors['FILE_IS_EMPTY'] = 'The uploaded file should not be blank';
            }

            if (!empty($_FILES['file_tmp']['name'])){
                $fileFormats = explode(',',SJB_System::getSettingByName('file_valid_types'));
                $fileInfo = pathinfo($_FILES['file_tmp']['name']);
                if (!isset($fileInfo['extension']) || !in_array(strtolower($fileInfo['extension']), $fileFormats)) {
                    $errors['NOT_SUPPORTED_FILE_FORMAT'] = strtolower($fileInfo['extension']) . ' ' . SJB_I18N::getInstance()->gettext(null, 'File format is not supported');
                }
            }
            
            if (empty($errors)) {
                $badge->addProperty(
                    array ( 'id'		=> 'file',
                            'type'		=> 'text',
                            'value'		=> $file_name,
                            'is_system' => true,
                            'default_value' => '',
                    )
                );
                 $badge->addProperty(
                    array ( 'id'		=> 'file_id',
                            'type'		=> 'text',
                            'value'		=> $id_file,
                            'is_system' => true,
                            'default_value' => '',
                    )
                );
                 $badge->addProperty(
                    array ( 'id'		=> 'mime_type',
                            'type'		=> 'string',
                            'value'		=> $mimeType,
                            'is_system' => true,
                            'default_value' => '',
                    )
                );
                
                $badge->saveBadge($badge);
                SJB_HelperFunctions::redirect(SJB_System::getSystemSettings("SITE_URL") . '/badges/' . strtolower($userGroup['id']) . '/');
            }
        }
        
		$errors = array_merge($errors, $badgeErrors);
		$formFieldsInfo = $addBadgeForm->getFormFieldsInfo();
		$formFields = array();
		foreach ($pages as $pageID => $page) {
			foreach ($formFieldsInfo as $formFieldInfo)
				if (in_array($formFieldInfo['id'], $page['fields']))
					$formFields[$pageID][] = $formFieldInfo;
			if (!isset($formFields[$pageID]))
				$formFields[$pageID] = array();
		}

		$tp->assign('form_fields', $formFields);
		$tp->assign('request', $_REQUEST);
		$tp->assign('params', http_build_query($_REQUEST));
		$tp->assign('pages', $pages);
		$tp->assign('userGroup', $userGroup);
		$tp->assign('errors', $errors);
		$tp->display('add_badge.tpl');
	}
}
