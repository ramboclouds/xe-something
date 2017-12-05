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

		$args = Context::getRequestVars();

		$mid_args=new stdClass;
		$mid_args->mid = $args->mid_name;
		$mid_args->module = 'something';
		$mid_args->layout_srl = $args->layout_srl;
		$mid_args->use_mobile = $args->use_mobile;
		$mid_args->mlayout_srl = $args->mlayout_srl;
		$mid_args->browser_title = $args->browser_title;
		$mid_args->site_srl = 0;
		$mid_args->skin = $args->skin;
		$mid_args->mskin = $args->mskin;
		$mid_args->header_text=$args->header_text;
		$mid_args->footer_text=$args->footer_text;
		$mid_args->mobile_header_text=$args->mobile_header_text;
		$mid_args->mobile_footer_text=$args->mobile_footer_text;

		if($args->origin_mid == "")
		{ // 신규 생성
			$module_info = $oModule->getModuleInfoByMid($args->mid_name);
			if(!$module_info->module_srl)
			{
				$oModuleController->insertModule($mid_args);

			}
			else
			{
				return new Object(-1, 'error_dup_mid');
			}

		}
		else
		{ //업데이트

			$mid_args->module_srl = $args->module_srl;
			$oModuleController->updateModule($mid_args);
		}

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
