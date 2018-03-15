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
		$oLayoutMode = getModel('layout');
		$oModuleModel = getModel('module');
		$oMemberModel = getModel('member');
		
		$config = $this->getConfig();
		if ($config->mid_name)
		{
			$module_info = $oModuleModel->getModuleInfoByMid($config->mid_name);
		}
		else
		{
			$module_info = new stdClass();
		}

		$skin_list = $oModuleModel->getSkins($this->module_path);
		$mskin_list = $oModuleModel->getSkins($this->module_path, "m.skins");

		$group_list = $oMemberModel->getGroups();

		$layout_list = $oLayoutMode->getLayoutList();
		$mobile_layout_list = $oLayoutMode->getLayoutList(0, "M");

		Context::set('module_info', $module_info);
		Context::set('skin_list', $skin_list);
		Context::set('config', $config);
		Context::set('mskin_list', $mskin_list);
		Context::set('group_list', $group_list);
		Context::set('layout_list', $layout_list);
		Context::set('mlayout_list', $mobile_layout_list);
	}

	function dispSomethingAdminConnect()
	{
		$config = $this->getConfig();
		
		Context::set('config', $config);
	}

	function dispSomethingAdminData()
	{
		$config = $this->getConfig();
		$mid_list = getModel('module')->getMidList(null, array('module_srl', 'mid', 'browser_title', 'module'));
		
		Context::set('config', $config);
		Context::set('mid_list', $mid_list);
	}

	function dispSomethingAdminSubscribe()
	{
		$config = $this->getConfig();
		$is_memberfollow_module = true;
		if (!is_object(getClass('memberfollow')))
		{
			$is_memberfollow_module = false;
		}
		Context::set('module_installed_memberfollow', $is_memberfollow_module);
		Context::set('config', $config);
	}

	function dispSomethingAdminSkinInfo()
	{
		$config = $this->getConfig();
		Context::set('config', $config);

		$oModuleModel = getModel('module');
		$module_info = $oModuleModel->getModuleInfoByMid($config->mid_name);
		Context::set('module_info', $module_info);


		$oModuleAdminModel = getAdminModel('module');
		$skin_content = $oModuleAdminModel->getModuleSkinHTML($module_info->module_srl);
		Context::set('skin_content', $skin_content);

		$this->setTemplateFile('skin_info');
	}

	function dispSomethingAdminMobileSkinInfo()
	{
		$config = $this->getConfig();
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
