<?php

class somethingAdminView extends something
{
	function init()
	{
		$this->setTemplatePath($this->module_path . 'tpl');
		$this->setTemplateFile(strtolower(str_replace('dispSomethingAdmin', '', $this->act)));
	}

	function dispSomethingAdminConfig()
	{
		$oSomethingModel = getModel('something');
		$config = $oSomethingModel->getConfig();
		Context::set('config', $config);

		$oModuleModel = getModel('module');

		if ($config->mid_name)
		{
			$module_info = $oModuleModel->getModuleInfoByMid($config->mid_name);
			Context::set('module_info', $module_info);
		}

		$skin_list = $oModuleModel->getSkins($this->module_path);
		Context::set('skin_list',$skin_list);

		$mskin_list = $oModuleModel->getSkins($this->module_path, "m.skins");
		Context::set('mskin_list', $mskin_list);
		
		$oMemberModel =&getModel('member');
		$output = $oMemberModel->getGroups();
		Context::set('group_list', $output);

		$oLayoutMode = getModel('layout');
		$layout_list = $oLayoutMode->getLayoutList();
		Context::set('layout_list', $layout_list);

		$mobile_layout_list = $oLayoutMode->getLayoutList(0,"M");
		Context::set('mlayout_list', $mobile_layout_list);
	}

	function dispSomethingAdminConnect()
	{
		$oSomethingModel = getModel('something');
		$config = $oSomethingModel->getConfig();
		Context::set('config', $config);
	}

	function dispSomethingAdminData()
	{
		$oSomethingModel = getModel('something');
		$config = $oSomethingModel->getConfig();
		Context::set('config', $config);
		$mid_list = getModel('module')->getMidList(null, array('module_srl', 'mid', 'browser_title', 'module'));
		Context::set('mid_list', $mid_list);
	}

	function dispSomethingAdminSubscribe()
	{
		$oSomethingModel = getModel('something');
		$config = $oSomethingModel->getConfig();
		$is_memberfollow_module = true;
		if (!is_object(getClass('memberfollow')))
		{
			$is_memberfollow_module = false;	
		}	 
		Context::set('module_installed_memberfollow', $is_memberfollow_module);
		Context::set('config', $config);
	}

	function dispSomethingAdminSkinInfo() {
		
		$oSomethingModel = getModel('something');
		$config = $oSomethingModel->getConfig();
		Context::set('config', $config);

		$oModuleModel = getModel('module');
		$module_info = $oModuleModel->getModuleInfoByMid($config->mid_name);
		Context::set('module_info', $module_info);
	

		$oModuleAdminModel = getAdminModel('module');
		$skin_content = $oModuleAdminModel->getModuleSkinHTML($module_info->module_srl);
		Context::set('skin_content', $skin_content);

		$this->setTemplateFile('skin_info');
	}

	function dispSomethingAdminMobileSkinInfo() {
		$oSomethingModel = getModel('something');
		$config = $oSomethingModel->getConfig();
		Context::set('config', $config);

		$oModuleModel = getModel('module');
		$module_info = $oModuleModel->getModuleInfoByMid($config->mid_name);
		Context::set('module_info', $module_info);
		
		$oModuleAdminModel = getAdminModel('module');
		$skin_content = $oModuleAdminModel->getModuleMobileSkinHTML($module_info->module_srl);
		Context::set('skin_content', $skin_content);

		$this->setTemplateFile('skin_info');
	}
}
/* End of file */
