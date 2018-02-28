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
			
			if (!$config->memeber_popupmenu_name){
				$config->memeber_popupmenu_name="회원 활동";
			}

			if (!$config->connect_address_type){
				$config->connect_address_type="member_srl";
			}

			if (!$config->thumbnail_width)
			{
				$config->thumbnail_width = 40;
			}

			if (!$config->thumbnail_height)
			{
				$config->thumbnail_height = 40;
			}

			if (!$config->thumbnail_type)
			{
				$config->thumbnail_type = "crop";
			}

			$this->config = $config;
		}

		return $this->config;
	}

	function getMemberInfoByNickName($nick_name,$mb_model=false)
	{
		if (!$nick_name) return;
		if (!$mb_model)
		{
			$mb_model=getModel('member');
		}

		$args = new stdClass;
		$args->nick_name = $nick_name;
		$output = executeQuery('something.getMemberInfoByNickName', $args);

		if (!$output->toBool()) return $output;
		if (!$output->data) return;
		$member_info = $mb_model->arrangeMemberInfo($output->data);
		return $member_info;
	}

	function getMemeberBoardData($memberInfo,$config){
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

		$tableColumnList = array('document_srl', 'module_srl', 'category_srl', 'lang_code', 'is_notice',
				'title', 'title_bold', 'title_color', 'content', 'readed_count', 'voted_count',
				'blamed_count', 'comment_count', 'trackback_count', 'uploaded_count', 'password', 'user_id',
				'user_name', 'nick_name', 'member_srl', 'email_address', 'homepage', 'tags', 'extra_vars',
				'regdate', 'last_update', 'last_updater', 'ipaddress', 'list_order', 'update_order',
				'allow_trackback', 'notify_message', 'status', 'comment_status');

		$oDocumentModel = getModel('document');
		$output = $oDocumentModel->getDocumentList($sObj, FALSE, TRUE, $tableColumnList);
		
		foreach ($output->data as $key => $value)
		{
			$output->data[$key]->doc_type="doc";
			$output->data[$key]->regdate = $value->getRegdate('YmdHis');
		}

		$cmt_output = executeQueryArray("something.getCommentData",$sObj);
		
		foreach ($cmt_output->data as $key => $value)
		{
			$cmt_output->data[$key]->doc_type="cmt";
			$cmt_output->data[$key]->content=strip_tags($value->content);
		}
	
		$output->data = array_merge((array) $output->data, (array) $cmt_output->data);
		usort($output->data,function($first,$second){
			return strtolower($first->regdate) < strtolower($second->regdate);
		});

		return $output;
	}

	function memberInfoReplace($memberInfo){
		if ($memberInfo->signature)
		{
			$memberInfo->signature=preg_replace('#<p(.*?)>(.*?)</p>#is', '$2<br/>', $memberInfo->signature);
			$memberInfo->signature=strip_tags($memberInfo->signature,"<br>");
		}

		return $memberInfo;
	}
}
/* End of file */
