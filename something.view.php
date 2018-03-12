<?php

class somethingView extends something
{
	function init()
	{
		// TODO(BJRambo) : get to something module skin setting by module_info
		$module_info = $this->module_info;
		$template_path = sprintf("%sskins/%s/", $this->module_path, $module_info->skin);

		if (!is_dir($template_path) || !$module_info->skin)
		{
			$module_info->skin = 'default';
			$template_path = sprintf("%sskins/%s/", $this->module_path, $module_info->skin);
		}
		$this->setTemplatePath($template_path);

		$oLayoutModel = getModel('layout');
		$layout_info = $oLayoutModel->getLayout($module_info->layout_srl);
		if ($layout_info)
		{
			$this->module_info->layout_srl = $module_info->layout_srl;
			$this->setLayoutPath($layout_info->path);
		}
	}

	function dispSomethingProfileView()
	{
		$user_string = Context::get('smember');
		
		Context::set('st_path', $this->module_path);
		if ($user_string == "")
		{
			
			Context::set('something_error_msg', lang('something_msg_user_notfound'));
			$this->setTemplateFile('_error');
			return;
		}

		$user_string = urldecode($user_string);

		$oSomethingModel = getModel('something');
		$oMemberModel = getModel('member');
		$oModuleModel = getModel('module');

		$config = $oSomethingModel->getConfig();

		if ($config->connect_address_type == 'user_id')
		{
			$memberInfo = $oMemberModel->getMemberInfoByUserId($user_string);
		}
		else if ($config->connect_address_type == 'member_srl')
		{
			$memberInfo = $oMemberModel->getMemberInfoByMemberSrl($user_string);
		}
		else if ($config->connect_address_type == 'nick_name')
		{
			$memberInfo = $oSomethingModel->getMemberInfoByNickName($user_string);
		}

		if (!$memberInfo->member_srl)
		{
			Context::set('something_error_msg', lang('something_msg_user_notfound'));
			$this->setTemplateFile('_error');
			return;
		}

		$module_info = $oModuleModel->getModuleInfoByMid($config->mid_name);
		
		$st_header_text = $module_info->header_text;
		$st_footer_text = $module_info->footer_text;
		
		if(Mobile::isMobileCheckByAgent()){
			$skin_vars = $oModuleModel->getModuleMobileSkinVars($module_info->module_srl);
			$st_header_text = $module_info->mobile_header_text;
			$st_footer_text = $module_info->mobile_footer_text;
		}else{
			$skin_vars = $oModuleModel->getModuleSkinVars($module_info->module_srl);
		}

		$skin_info = $oSomethingModel->convertSkinVars($skin_vars);

		$memberInfo = $oSomethingModel->memberInfoReplace($memberInfo);
		$boardData = $oSomethingModel->getMemeberBoardData($memberInfo, $config, Context::getRequestVars(),$skin_info);

		Context::set('total_count', $boardData->total_count);
		Context::set('total_page', $boardData->total_page);
		Context::set('page', $boardData->page);
		Context::set('page_navigation', $boardData->page_navigation);

		Context::set('module_info', $module_info);
		Context::set('skin_info', $skin_info);

		Context::set('board_data', $boardData->data);
		Context::set('member_info', $memberInfo);

		Context::set('st_config', $config);

		Context::set('st_header_text', $st_header_text);
		Context::set('st_footer_text', $st_footer_text);

		$this->setTemplateFile('profile');
	}
}
/* End of file */
