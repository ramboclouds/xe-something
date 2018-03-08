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

		$moduleSrltoMid = $this->getMidCache($config);


		foreach ($output->data as $key => $value)
		{
			$output->data[$key]->doc_type = "doc";
			$output->data[$key]->regdate = $value->getRegdate('YmdHis');
			$output->data[$key]->mid = $moduleSrltoMid[$value->get('module_srl')];
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

	function getMidCache($config=false)
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
			foreach($output->data as $key=>$val){
				if ($mid_tmp == "")
				{
					$mid_tmp = $val->module_srl.'=>"'.$val->mid.'"';
				}
				else 
				{
					$mid_tmp = $mid_tmp.','.$val->module_srl.'=>"'.$val->mid.'"';
				}
			}

			$mid_data="<?php \$st_module_srl_to_mid=array(".$mid_tmp."); ?>";
			$wr_file   = fopen($cache_data, "w");
			$pieces = str_split($mid_data, 1024 * 4);
			foreach ($pieces as $piece) {
				fwrite($wr_file, $piece, strlen($piece));
			}
			fclose($wr_file);
			@chmod($cache_data,0707);
			
		}

		if (!file_exists($cache_data))
		{
			return array();
		}

		include_once($cache_data);
		return $st_module_srl_to_mid;

	}
}
/* End of file */
