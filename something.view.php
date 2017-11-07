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
		$userId = Context::get('user_id');

		$memberInfo = getModel('member')->getMemberInfoByUserId($userId);

		Context::set('user_id', $userId);
		Context::set('member_info', $memberInfo);
		$this->setTemplateFile('profile');
	}
}