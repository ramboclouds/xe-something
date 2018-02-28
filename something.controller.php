<?php
class somethingController extends something
{
	function triggerAddMemberMenu(&$module_info)
	{
		$config = getModel('something')->getConfig();
		if ($config->use != "Y")
		{
			return;
		}

		$oMemberController = getController('member');
		$mbModel = getModel('member');

		$target_srl = Context::get('target_srl');
		$target_mid = $config->mid_name;

		$memberInfo = $mbModel->getMemberInfoByMemberSrl($target_srl);
		$url = getUrl('', 'mid', $target_mid, 'smember', $target_srl);

		if ($config->connect_address_type == 'user_id')
		{
			$url = getUrl('', 'mid', $target_mid, 'smember', $memberInfo->user_id);
		}
		else if ($config->connect_address_type == 'nick_name')
		{
			$url = getUrl('', 'mid', $target_mid, 'smember', $memberInfo->nick_name);
		}

		$str = $config->memeber_popupmenu_name;
		$oMemberController->addMemberPopupMenu($url, $str, '','self');
	}
}
/* End of file */
