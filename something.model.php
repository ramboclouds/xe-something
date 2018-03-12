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

	function getMemeberBoardData($memberInfo, $config, $args, $skin_info)
	{
		$board_srls = null;

		if (count($config->board_module_srls) > 0)
		{
			$board_srls = implode($config->board_module_srls, ",");
		}

		$skin_info = $this->replaceSkinInfo($skin_info);

		$sObj = new stdClass();
		$sObj->member_srl = $memberInfo->member_srl;
		$sObj->module_srl = $board_srls;
		$sObj->statusList = "PUBLIC";
		$sObj->sort_index = "regdate";
		$sObj->order_type = "desc";
		$sObj->page = $args->page;
		$sObj->page_count = $skin_info->page_count;
		$sObj->list_count = $skin_info->list_count;

		$oModuleSrl = $this->getModuleInfoCache($config);

		if ($args->view_type != "comment")
		{

			/** @var documentModel $oDocumentModel */
			$oDocumentModel = getModel('document');
			$output = $oDocumentModel->getDocumentList($sObj, FALSE, TRUE);

			foreach ($output->data as $key => $value)
			{
				$output->data[$key]->doc_type = "doc";
				$output->data[$key]->regdate = $value->get('regdate');
				$output->data[$key]->mid = $oModuleSrl->mid[$value->get('module_srl')];
				$output->data[$key]->browser_title = $oModuleSrl->browser_title[$value->get('module_srl')];
			}
		
		}

		$commentOutput = executeQueryArray("something.getCommentData", $sObj);

		foreach ($commentOutput->data as $key => $value)
		{
			$commentOutput->data[$key]->doc_type = "cmt";
			$cmt_content = strip_tags($value->content);
			$cmt_content_blank = str_replace(array('&nbsp;'),array(''),$cmt_content);
			if(trim($cmt_content_blank) == ""){
				$cmt_content = Context::getLang('something_message_comment_content_blank');
			}
			$commentOutput->data[$key]->content = $cmt_content;
			$commentOutput->data[$key]->mid = $moduleSrltoMid[$value->module_srl];
			$commentOutput->data[$key]->browser_title = $oModuleSrl->browser_title[$value->module_srl];
		}

		if ($args->view_type == "comment")
		{
			return $commentOutput;
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

	function getModuleInfoCache($config=false)
	{
		if (!$config)
		{
			$config=$this->getConfig();
		}

		if ($config->ht_use == "N")
		{
			return;
		}

		//디렉토리 검사
		$cache_folder=_XE_PATH_."files/cache/something";
		$cache_data=$cache_folder."/srltomid.php";

		if (!is_dir($cache_folder))
		{
			mkdir($cache_folder,0707);
			@chmod($cache_folder,0707);
		}
		
		//ok,no,del
		$data_make="no";

		if (file_exists($cache_data))
		{
			$last_mod=date("YmdHis", filemtime($cache_data));
			$now_dt=date("YmdHis" , strtotime('-60 minutes') );
			if ($now_dt > $last_mod) 
			{
				$data_make = "ok";
			}
		}
		else
		{
			$data_make = "ok";
		}

		
		if ($data_make == "ok")
		{
			$output=executeQueryArray('something.getMid');
			$mid_tmp="";
			$name_tmp="";
			foreach($output->data as $key=>$val){
				if ($mid_tmp == "")
				{
					$mid_tmp = $val->module_srl.'=>"'.$val->mid.'"';
					$name_tmp = $val->module_srl.'=>"'.$val->browser_title.'"';
				}
				else 
				{
					$mid_tmp = $mid_tmp.','.$val->module_srl.'=>"'.$val->mid.'"';
					$name_tmp = $name_tmp.','.$val->module_srl.'=>"'.$val->browser_title.'"';
				}
			}

			$mid_data="<?php \$st_module_srl_to_mid = array(".$mid_tmp."); ".PHP_EOL."\$st_module_srl_to_name = array(".$name_tmp.");?>";
			$wr_file   = fopen($cache_data, "w");
			$pieces = str_split($mid_data, 1024 * 4);
			foreach ($pieces as $piece) {
				fwrite($wr_file, $piece, strlen($piece));
			}
			fclose($wr_file);
			@chmod($cache_data,0707);
			unset($mid_data);
		}

		$ret_obj = new stdClass();
		

		if (!file_exists($cache_data))
		{
			$ret_obj->mid = array();
			$ret_obj->browser_title = array();
			return $ret_obj;
		}

		include_once($cache_data);
		$ret_obj->mid = $st_module_srl_to_mid;
		$ret_obj->browser_title = $st_module_srl_to_name;		
		return $ret_obj;

	}

	function replaceSkinInfo($skin_info){
		$st_skin_info = $skin_info;
	
		if(!$st_skin_info->use_page) $st_skin_info->use_page = 'Y';
		if(!$st_skin_info->list_count) $st_skin_info->list_count = 10;
		if(!$st_skin_info->page_count) $st_skin_info->page_count = 5;

		return $st_skin_info;
	}

	function convertSkinVars($skin_vars){
	
		$ret_obj = new stdClass();
		foreach ($skin_vars as $key => $val)
		{
			
			$ret_obj->$key=$val->value;
		}

		return $ret_obj;
	}
}
/* End of file */
