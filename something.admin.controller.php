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
}
/* End of file */
