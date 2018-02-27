<?php

class somethingAdminView extends something
{
	// TODO(BJRambo): Add to member Variables for module Info

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

		$oModuleModel = &getModel('module');

		if ($config->mid_name != "")
		{
			Context::set('module_info', $oModuleModel->getModuleInfoByMid($config->mid_name));
		}

		$skin_list = $oModuleModel->getSkins($this->module_path);
		Context::set('skin_list',$skin_list);

		$mskin_list = $oModuleModel->getSkins($this->module_path, "m.skins");
		Context::set('mskin_list', $mskin_list);


		$oLayoutMode = &getModel('layout');
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

	function dispSomethingAdminModuleInstance()
	{
		$modulePath = $this->module_path;

		$moduleSrl = Context::get('module_srl');
		if(!$moduleSrl)
		{
			if($this->module_srl)
			{
				$moduleSrl = $this->module_srl;
			}
		}

		$moduleInfo = getModel('module')->getModuleInfoByModuleSrl($moduleSrl);

		Context::set('module_info', $moduleInfo);

		$oModuleModel = getModel('module');
		$skinList = $oModuleModel->getSkins($modulePath);
		$mSkinList = $oModuleModel->getSkins($modulePath, 'm.skins');

		/** @var $oLayoutModel layoutModel */
		$oLayoutModel = getModel('layout');
		$layoutList = $oLayoutModel->getLayoutList();
		$mLayoutList = $oLayoutModel->getLayoutList(0, 'M');

		Context::set('skin_list', $skinList);
		Context::set('mskin_list', $mSkinList);
		Context::set('layout_list', $layoutList);
		Context::set('mlayout_list', $mLayoutList);
	}
}
/* End of file */
