<?php
class somethingView extends something
{
	function init()
	{
		// TODO(BJRambo) : get to something module skin setting by module_info
		$module_info = $this->module_info;
		$template_path = sprintf("%sskins/%s/",$this->module_path, $module_info->skin);
		if(!is_dir($template_path)||!$module_info->skin)
		{
			$module_info->skin = 'default';
			$template_path = sprintf("%sskins/%s/",$this->module_path, $module_info->skin);
		}
		$this->setTemplatePath($template_path);

		$oLayoutModel = getModel('layout');
		$layout_info = $oLayoutModel->getLayout($module_info->layout_srl);

		if($layout_info)
		{
			$this->module_info->layout_srl = $module_info->layout_srl;
			$this->setLayoutPath($layout_info->path);
		}
	}

	function dispSomethingProfileView()
	{
		// index.php?mid=sometest&smember=테스트아이디

		$user_string = Context::get('smember');
		
		if ($user_string == "")
		{
			Context::set('something_error_msg',lang('something_msg_user_notfound'));
			$this->setTemplateFile('_error');
			return;
		}

		$stModel = getModel('something');
		$mbModel = getModel('member');
		$config = $stModel->getConfig();

		if ($config->connect_address_type == 'user_id')
		{
			$memberInfo = $mbModel->getMemberInfoByUserId($user_string);
		}
		else if ($config->connect_address_type == 'member_srl')
		{
			$memberInfo = $mbModel->getMemberInfoByMemberSrl($user_string);
		}
		else if ($config->connect_address_type == 'nick_name')
		{
			$memberInfo = $stModel->getMemberInfoByNickName($user_string,$mbModel);
		}

		if (!$memberInfo->member_srl)
		{
			Context::set('something_error_msg',lang('something_msg_user_notfound'));
			$this->setTemplateFile('_error');
			return;
		}
		$memberInfo = $stModel->memberInfoReplace($memberInfo);
		$boardData = $stModel->getMemeberBoardData($memberInfo,$config);
		
		Context::set('board_data', $boardData->data);
		Context::set('member_info', $memberInfo);
		Context::set('st_config', $config);
		$this->setTemplateFile('profile');
	}
}
/* End of file */
