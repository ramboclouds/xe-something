<?php

class somethingView extends something
{
	function init()
	{
		$module_info = $this->module_info;
		$template_path = sprintf("%sskins/%s/", $this->module_path, $module_info->skin);

		if (!is_dir($template_path) || !$module_info->skin)
		{
			$module_info->skin = 'default';
			$template_path = sprintf("%sskins/%s/", $this->module_path, $module_info->skin);
		}
		$this->setTemplatePath($template_path);

		$oLayoutModel = getModel('layout');
		$layout_info = $oLayoutModel->getLayout($module_info->layout_srl);
		if ($layout_info)
		{
			$this->module_info->layout_srl = $module_info->layout_srl;
			$this->setLayoutPath($layout_info->path);
		}
	}

	function dispSomethingProfileView()
	{
		$user_string = Context::get('smember');

		Context::set('st_path', $this->module_path);
		if ($user_string == "")
		{
			Context::set('something_error_msg', lang('something_msg_user_notfound'));
			$this->setTemplateFile('_error');
			return;
		}

		$user_string = urldecode($user_string);

		$oSomethingModel = getModel('something');
		$oMemberModel = getModel('member');
		$oModuleModel = getModel('module');

		$config = $this->getConfig();

		if ($config->connect_address_type == 'user_id')
		{
			$memberInfo = $oMemberModel->getMemberInfoByUserId($user_string);
		}
		else if ($config->connect_address_type == 'member_srl')
		{
			$memberInfo = $oMemberModel->getMemberInfoByMemberSrl($user_string);
		}
		else if ($config->connect_address_type == 'nick_name')
		{
			$memberInfo = $oSomethingModel->getMemberInfoByNickName($user_string);
		}

		if (!$memberInfo->member_srl)
		{
			Context::set('something_error_msg', lang('something_msg_user_notfound'));
			$this->setTemplateFile('_error');
			return;
		}

		$memberInfo->follow_count = 0;
		$logged_info = Context::get('logged_info');

		$oMemberModel = getModel('member');

		if ($config->group == "all")
		{
			$is_permitted = true;
		}
		else
		{
			if (!$logged_info->member_srl)
			{
				$is_permitted = false;
			}
			else
			{
				$member_groups = $oMemberModel->getMemberGroups($logged_info->member_srl);
				$is_permitted = false;
				for ($i = 0; $i < count($config->group); $i++)
				{
					$group_srl = $config->group[$i];
					if ($member_groups[$group_srl])
					{
						$is_permitted = true;
						break;
					}
				}
			}
		}

		if (!$is_permitted)
		{
			Context::set('something_error_msg', lang('something_permission_denied'));
			$this->setTemplateFile('_error');
			return;
		}

		$module_info = $oModuleModel->getModuleInfoByMid($config->mid_name);
		$recent_activity = $oSomethingModel->getMemeberRecentActivity($memberInfo);

		$st_header_text = $module_info->header_text;
		$st_footer_text = $module_info->footer_text;

		if (Mobile::isMobileCheckByAgent())
		{
			$skin_vars = $oModuleModel->getModuleMobileSkinVars($module_info->module_srl);
			$st_header_text = $module_info->mobile_header_text;
			$st_footer_text = $module_info->mobile_footer_text;
		}
		else
		{
			$skin_vars = $oModuleModel->getModuleSkinVars($module_info->module_srl);
		}

		$skin_info = $oSomethingModel->convertSkinVars($skin_vars);
		$memberInfo = $oSomethingModel->memberInfoReplace($memberInfo);
		$is_memberfollow_module = true;
		if (!is_object(getClass('memberfollow')))
		{
			$is_memberfollow_module = false;
			$memberInfo->follower_count = 0;
		}

		if (Context::get('view_type') == "followerlist" || Context::get('view_type') == "followinglist")
		{
			if ($config->subscribe_use == "N" || !$is_memberfollow_module)
			{
				Context::set('something_error_msg', lang('something_access_denied'));
				$this->setTemplateFile('_error');
				return;
			}
		}

		if (Context::get('view_type') == "recommend")
		{
			$somethingData = $oSomethingModel->getMemberVotedList($memberInfo, $config, Context::getRequestVars(), $skin_info);
		}
		else if (Context::get('view_type') == "followerlist")
		{
			if ($config->subscribe_click_action != "list")
			{
				Context::set('something_error_msg', lang('something_access_denied'));
				$this->setTemplateFile('_error');
				return;
			}

			$somethingData = $oSomethingModel->getMemberFollowerList($memberInfo, $config, Context::getRequestVars(), $skin_info);
		}
		else if (Context::get('view_type') == "followinglist")
		{
			if ($config->subscribe_follow_view_use == "N")
			{
				Context::set('something_error_msg', lang('something_access_denied'));
				$this->setTemplateFile('_error');
				return;
			}

			if (!$logged_info->member_srl)
			{
				Context::set('something_error_msg', lang('something_access_denied'));
				$this->setTemplateFile('_error');
				return;
			}

			if ($memberInfo->member_srl != $logged_info->member_srl)
			{
				Context::set('something_error_msg', lang('something_access_denied'));
				$this->setTemplateFile('_error');
				return;
			}

			$somethingData = $oSomethingModel->getFollowingList($memberInfo, $config, Context::getRequestVars(), $skin_info);
		}
		else
		{
			$somethingData = $oSomethingModel->getMemeberBoardData($memberInfo, $config, Context::getRequestVars(), $skin_info);
		}

		if ($is_memberfollow_module)
		{
			$followOutput = executeQuery('memberfollow.getMemberFollowerCount', $memberInfo);
			$memberInfo->follower_count = $followOutput->data->cnt;
		}

		if ($oSomethingModel->checkMobile())
		{
			$config->subscribe_follow_view_menu_name = $config->subscribe_follow_view_menu_name_mobile;
		}

		Context::set('module_installed_memberfollow', $is_memberfollow_module);

		Context::set('total_count', $somethingData->total_count);
		Context::set('total_page', $somethingData->total_page);
		Context::set('page', $somethingData->page);
		Context::set('page_navigation', $somethingData->page_navigation);

		Context::set('module_info', $module_info);
		Context::set('skin_info', $skin_info);

		Context::set('something_data', $somethingData->data);
		Context::set('member_info', $memberInfo);
		Context::set('recent_activity', $recent_activity);

		Context::set('st_config', $config);

		Context::set('st_header_text', $st_header_text);
		Context::set('st_footer_text', $st_footer_text);

		$this->setTemplateFile('profile');
	}
}
/* End of file */
