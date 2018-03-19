<?php

class somethingMobile extends somethingView
{
	function init()
	{
		
        $module_info = $this->module_info;
		$template_path = sprintf("%sm.skins/%s/",$this->module_path, $module_info->mskin);
		if(!is_dir($template_path)||!$config->mskin)
		{
			$config->skin = 'default';
			$template_path = sprintf("%sm.skins/%s/",$this->module_path, $module_info->mskin);
		}
		$this->setTemplatePath($template_path);

		$oLayoutModel = getModel('layout');
		$layout_info = $oLayoutModel->getLayout($module_info->mlayout_srl);

		if($layout_info)
		{
			$this->setLayoutPath($layout_info->path);
		}

	}

}
