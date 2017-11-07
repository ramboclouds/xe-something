<?php

class somethingAdminController extends something
{
	function init()
	{
	}

	function procSomethingAdminInsertConfig()
	{
		/** @var $oModuleController moduleController */
		$oModuleController = getController('module');
		$config = new stdClass();
		$config->use = Context::get('use');

		$output = $oModuleController->updateModuleConfig('something', $config);
		if(!$output->toBool())
		{
			return $output;
		}

		$this->setMessage('success_updated');

		$successReturnUrl = Context::get('success_return_url');
		if ($successReturnUrl)
		{
			$this->setRedirectUrl($successReturnUrl);
		}
		else
		{
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispSomethingAdminConfig'));
		}
	}

	function procSomethingAdminInsertModuleInstance()
	{
		$obj = Context::getRequestVars();

		if ($obj->module !== 'something')
		{
			return new Object(-1, 'Do not set to module.');
		}

		/** @var $oModuleController moduleController */
		$oModuleController = getController('module');

		if ($obj->module_srl)
		{
			$output = $oModuleController->updateModule($obj);
		}
		else
		{
			$obj->module_srl = getNextSequence();
			$output = $oModuleController->insertModule($obj);
		}
		if (!$output->toBool())
		{
			return $output;
		}


		$this->setMessage('success_updated');

		$successReturnUrl = Context::get('success_return_url');
		if ($successReturnUrl)
		{
			$this->setRedirectUrl($successReturnUrl);
		}
		else
		{
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispSomethingAdminModuleInstance', 'module_srl', $output->get('module_srl')));
		}
	}

}
/* End of file */
