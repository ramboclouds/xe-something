<?php

class somethingAdminController extends something
{
	function init()
	{
	}

	function procSomethingAdminInsertConfig()
	{
		/** @var $oModuleController moduleController */
		$obj = Context::getRequestVars();

		$oModuleController = getController('module');
		$config = $this->getConfig();
		$config->use = Context::get('use');
		$config->mid_name = $obj->mid_name;
		if (count($obj->group) == 0)
		{
			$obj->group = "all";
		}

		$config->group = $obj->group;

		$mid_args = new stdClass;
		$mid_args->mid = $obj->mid_name;
		$mid_args->module = 'something';
		$mid_args->layout_srl = $obj->layout_srl;
		$mid_args->use_mobile = $obj->use_mobile;
		$mid_args->mlayout_srl = $obj->mlayout_srl;
		$mid_args->browser_title = $obj->browser_title;
		$mid_args->site_srl = 0;
		$mid_args->skin = $obj->skin;
		$mid_args->mskin = $obj->mskin;
		$mid_args->header_text = $obj->header_text;
		$mid_args->footer_text = $obj->footer_text;
		$mid_args->mobile_header_text = $obj->mobile_header_text;
		$mid_args->mobile_footer_text = $obj->mobile_footer_text;

		if ($obj->origin_mid == "")
		{
			$module_info = getModel('module')->getModuleInfoByMid($obj->mid_name);
			if (!$module_info->module_srl)
			{
				$insertOutput = $oModuleController->insertModule($mid_args);
				if (!$insertOutput->toBool())
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
			$mid_args->module_srl = $obj->module_srl;
			$updateOutput = $oModuleController->updateModule($mid_args);
			if (!$updateOutput->toBool())
			{
				return $updateOutput;
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

	function procSomethingAdminInsertConnect()
	{
		$oModuleController = getController('module');

		$obj = Context::getRequestVars();

		$config = $this->getConfig();
		$config->connect_address_type = $obj->connect_address_type;
		$config->memeber_popupmenu_name = $obj->memeber_popupmenu_name;

		$output = $oModuleController->insertModuleConfig('something', $config);
		if (!$output->toBool())
		{
			return $output;
		}

		$this->setMessage("success_saved");

		$successReturnUrl = Context::get('success_return_url');
		if ($successReturnUrl)
		{
			$this->setRedirectUrl($successReturnUrl);
		}
		else
		{
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispSomethingAdminConnect'));
		}
	}

	function procSomethingAdminInsertData()
	{
		$obj = Context::getRequestVars();
		$config = $this->getConfig();

		if (!$obj->board_module_srls)
		{
			$config->board_module_srls = array();
		}
		else
		{
			$config->board_module_srls = $obj->board_module_srls;
		}

		$oModuleController = getController('module');
		$oModuleController->insertModuleConfig('something', $config);

		$this->setMessage("success_saved");

		$successReturnUrl = Context::get('success_return_url');
		if ($successReturnUrl)
		{
			$this->setRedirectUrl($successReturnUrl);
		}
		else
		{
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispSomethingAdminData'));
		}
	}

	function procSomethingAdminInsertSubscribe()
	{
		$obj = Context::getRequestVars();
		$config = $this->getConfig();

		$config->subscribe_use = $obj->subscribe_use;
		$config->subscribe_click_action = $obj->subscribe_click_action;
		$config->subscribe_follow_view_use = $obj->subscribe_follow_view_use;
		$config->subscribe_follow_view_menu_name = $obj->subscribe_follow_view_menu_name;

		$oModuleController = getController('module');
		$oModuleController->insertModuleConfig('something', $config);

		$this->setMessage('success_updated');
		$successReturnUrl = Context::get('success_return_url');
		if ($successReturnUrl)
		{
			$this->setRedirectUrl($successReturnUrl);
		}
		else
		{
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispSomethingAdminSubscribe'));
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
