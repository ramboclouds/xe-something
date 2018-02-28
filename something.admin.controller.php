<?php

class somethingAdminController extends something
{
	function init()
	{
	}

	function procSomethingAdminInsertConfig()
	{
		/** @var $oModuleController moduleController */
		$args = Context::getRequestVars();

		$oModuleController = getController('module');
		$config = new stdClass();
		$config->use = Context::get('use');
		$config->mid_name=$args->mid_name;

		$mid_args = new stdClass;
		$mid_args->mid = $args->mid_name;
		$mid_args->module = 'something';
		$mid_args->layout_srl = $args->layout_srl;
		$mid_args->use_mobile = $args->use_mobile;
		$mid_args->mlayout_srl = $args->mlayout_srl;
		$mid_args->browser_title = $args->browser_title;
		$mid_args->site_srl = 0;
		$mid_args->skin = $args->skin;
		$mid_args->mskin = $args->mskin;
		$mid_args->header_text = $args->header_text;
		$mid_args->footer_text = $args->footer_text;
		$mid_args->mobile_header_text = $args->mobile_header_text;
		$mid_args->mobile_footer_text = $args->mobile_footer_text;

		if ($args->origin_mid == "")
		{
			$module_info = getModel('module')->getModuleInfoByMid($args->mid_name);
			if (!$module_info->module_srl)
			{
				$insertOutput = $oModuleController->insertModule($mid_args);
				if(!$insertOutput->toBool())
				{
					return $insertOutput;
				}
			}
			else
			{
				return $this->makeObject(-1, 'error_dup_mid');
			}

		}
		else
		{
			$mid_args->module_srl = $args->module_srl;
			$midOutput = $oModuleController->updateModule($mid_args);
			if(!$midOutput->toBool())
			{
				return $midOutput;
			}
		}

		$output = $oModuleController->insertModuleConfig('something', $config);
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
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispSomethingAdminConfig'));
		}
	}

	function procSomethingAdminInsertConnect(){
		$args = Context::getRequestVars();
		$config = getModel('something')->getConfig();
		$config->connect_address_type = $args->connect_address_type;
		$config->memeber_popupmenu_name = $args->memeber_popupmenu_name;
		
		$oModuleController = &getController('module');
		$output = $oModuleController->insertModuleConfig('something', $config);
		$this->setMessage("success_saved");
		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))){
			$returnUrl = getNotEncodedUrl('', 'module', 'admin', 'act', $args->disp_act);
			header('location: ' . $returnUrl);
			return;
		}
	}

	function procSomethingAdminInsertData(){
		$args = Context::getRequestVars();
		$config = getModel('something')->getConfig();

		if (!$args->board_module_srls){
			$config->board_module_srls =array();
		}else{
			$config->board_module_srls=$args->board_module_srls;
		}

		$oModuleController = getController('module');
		$oModuleController->insertModuleConfig('something', $config);
		
		$this->setMessage("success_saved");
		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))){
			$returnUrl = getNotEncodedUrl('', 'module', 'admin', 'act', $args->disp_act);
			header('location: ' . $returnUrl);
			return;
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
