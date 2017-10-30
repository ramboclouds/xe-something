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
	}
}
/* End of file */
