<?php

class somethingModel extends something
{
	private $config = NULL;

	function getConfig()
	{
		if ($this->config === NULL)
		{
			/** @var $oModuleModel moduleModel */
			$oModuleModel = getModel('module');
			$config = $oModuleModel->getModuleConfig('something');

			if (!$config)
			{
				$config = new stdClass();
			}

			if (!$config->use)
			{
				$config->use = 'N';
			}

			if (!$config->board_module_srls)
			{
				$config->board_module_srls = array();
			}

			if (!$config->memeber_popupmenu_name)
			{
				$config->memeber_popupmenu_name = "회원 활동";
			}

			if (!$config->connect_address_type)
			{
				$config->connect_address_type = "member_srl";
			}

		


			$this->config = $config;
		}

		return $this->config;
	}

	function getMemberInfoByNickName($nick_name)
	{
		$oMemberModel = getModel('member');
		if (!$nick_name)
		{
			return;
		}

		$args = new stdClass;
		$args->nick_name = $nick_name;
		$output = executeQuery('something.getMemberInfoByNickName', $args);

		if (!$output->toBool())
		{
			return $output;
		}
		if (!$output->data)
		{
			return;
		}
		$member_info = $oMemberModel->arrangeMemberInfo($output->data);

		return $member_info;
	}

	function getMemeberBoardData($memberInfo, $config)
	{
		$board_srls = null;

		if (count($config->board_module_srls) > 0)
		{
			$board_srls = implode($config->board_module_srls, ",");
		}

		$sObj = new stdClass();
		$sObj->member_srl = $memberInfo->member_srl;
		$sObj->module_srl = $board_srls;
		$sObj->statusList = "PUBLIC";
		$sObj->sort_index = "regdate";
		$sObj->order_type = "desc";
		$sObj->list_count = 20;

		/** @var documentModel $oDocumentModel */
		$oDocumentModel = getModel('document');
		$output = $oDocumentModel->getDocumentList($sObj, FALSE, TRUE);

		foreach ($output->data as $key => $value)
		{
			$output->data[$key]->doc_type = "doc";
			$output->data[$key]->regdate = $value->getRegdate('YmdHis');
		}

		$commentOutput = executeQueryArray("something.getCommentData", $sObj);

		foreach ($commentOutput->data as $key => $value)
		{
			$commentOutput->data[$key]->doc_type = "cmt";
			$commentOutput->data[$key]->content = strip_tags($value->content);
		}

		$output->data = array_merge((array)$output->data, (array)$commentOutput->data);
		usort($output->data, function($first, $second){
			return strtolower($first->regdate) < strtolower($second->regdate);
		});

		return $output;
	}

	function memberInfoReplace($memberInfo)
	{
		if ($memberInfo->signature)
		{
			$memberInfo->signature = preg_replace('#<p(.*?)>(.*?)</p>#is', '$2<br/>', $memberInfo->signature);
			$memberInfo->signature = strip_tags($memberInfo->signature, "<br>");
		}

		return $memberInfo;
	}
}
/* End of file */
