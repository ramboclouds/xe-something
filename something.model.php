<?php

class somethingModel extends something
{
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

		$commentOutput = executeQueryArray("something.getMemberCommentList", $sObj);

		foreach ($commentOutput->data as $key => $value)
		{
			$commentOutput->data[$key]->doc_type = "cmt";
			$cmt_content = strip_tags($value->content);
			$cmt_content_blank = str_replace(array('&nbsp;'), array(''), $cmt_content);
			if (trim($cmt_content_blank) == "")
			{
				$cmt_content = Context::getLang('something_message_comment_content_blank');
			}
			$commentOutput->data[$key]->content = $cmt_content;
			// TODO(clouds): check agine is $moduleSrltoMid
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

	function getMemberVotedList($memberInfo, $config, $args, $skin_info)
	{
		$skin_info = $this->replaceSkinInfo($skin_info);
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
		$sObj->page = $args->page;
		$sObj->page_count = $skin_info->page_count;
		$sObj->list_count = $skin_info->list_count;

		$output = executeQueryArray("something.getMemberVotedList", $sObj);
		if ($output->data)
		{
			$doc_srls = array();
			foreach ($output->data as $key => $value)
			{
				array_push($doc_srls, $value->document_srl);
			}

			if (count($doc_srls) > 0)
			{
				$documents = getModel('document')->getDocuments($doc_srls);
			}

			if ($documents)
			{
				foreach ($output->data as $key => $value)
				{
					$output->data[$key] = $documents[$value->document_srl];
				}
			}

		}
		return $output;
	}

	function getMemberFollowerList($memberInfo, $config, $args, $skin_info)
	{
		$skin_info = $this->replaceSkinInfo($skin_info);

		$sObj = new stdClass();
		$sObj->member_srl = $memberInfo->member_srl;
		$sObj->sort_index = "regdate";
		$sObj->order_type = "desc";
		$sObj->page = $args->page;
		$sObj->page_count = $skin_info->page_count;
		$sObj->list_count = $skin_info->list_count;

		$oMemberModel = getModel('member');

		$output = executeQueryArray('something.getMemberFollowerList', $sObj);
		if ($output->data)
		{
			foreach ($output->data as $key => $value)
			{
				$output->data[$key] = $oMemberModel->arrangeMemberInfo($value, 0);
				$output->data[$key]->signature = strip_tags($output->data[$key]->signature);

				if (trim($output->data[$key]->signature) == "")
				{
					$output->data[$key]->signature = lang('something_message_empty_signature');
				}
				$output->data[$key]->smember = $value->{$config->connect_address_type};
				$followOutput = executeQuery('memberfollow.getMemberFollowerCount', $value);
				$output->data[$key]->follower_count = $followOutput->data->cnt;

				if (!$value->recent_activity)
				{
					$output->data[$key]->recent_activity = $this->getMemeberRecentActivity($value);
				}
			}
		}
		return $output;
	}

	function getFollowingList($memberInfo, $config, $args, $skin_info)
	{

		$fObj = new stdClass();
		$fObj->member_srl = $memberInfo->member_srl;

		$fOutput = executeQueryArray('something.getMemberFollowingList', $fObj);
		$member_srls = array();

		foreach ($fOutput->data as $key => $value)
		{
			array_push($member_srls, $value->target_srl);
		}

		if (count($member_srls) == 0)
		{
			return;
		}

		$skin_info = $this->replaceSkinInfo($skin_info);

		$board_srls = null;

		if (count($config->board_module_srls) > 0)
		{
			$board_srls = implode($config->board_module_srls, ",");
		}


		$sObj = new stdClass();
		$sObj->member_srls = implode($member_srls, ",");
		$sObj->module_srl = $board_srls;
		$sObj->statusList = "PUBLIC";
		$sObj->sort_index = "regdate";
		$sObj->order_type = "desc";
		$sObj->page = $args->page;
		$sObj->page_count = $skin_info->page_count;
		$sObj->list_count = $skin_info->list_count;

		$oDocumentModel = getModel('document');
		$output = $oDocumentModel->getDocumentList($sObj, FALSE, TRUE);
		$oModuleSrl = $this->getModuleInfoCache($config);

		foreach ($output->data as $key => $value)
		{
			$output->data[$key]->doc_type = "doc";
			$output->data[$key]->regdate = $value->get('regdate');
			$output->data[$key]->mid = $oModuleSrl->mid[$value->get('module_srl')];
			$output->data[$key]->browser_title = $oModuleSrl->browser_title[$value->get('module_srl')];
		}

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

	function getModuleInfoCache($config = false)
	{
		if (!$config)
		{
			$config = $this->getConfig();
		}

		if ($config->ht_use == "N")
		{
			return;
		}

		$cache_folder = _XE_PATH_ . "files/cache/something";
		$cache_data = $cache_folder . "/srltomid.php";

		//TODO(clouds): files 폴더를 사용하면 권한을 바꾸는 것은 위험합니다.(각 서버에서 일부러 사용하는 권한종류가 다 다르니 마음대로 고치는 소스는 불필요합니다..)
		if (!is_dir($cache_folder))
		{
			mkdir($cache_folder, 0707);
			@chmod($cache_folder, 0707);
		}

		$data_make = "no";    //ok,no,del

		if (file_exists($cache_data))
		{
			$last_mod = date("YmdHis", filemtime($cache_data));
			$now_dt = date("YmdHis", strtotime('-60 minutes'));
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
			$output = executeQueryArray('something.getMidList');
			$mid_tmp = "";
			$name_tmp = "";
			foreach ($output->data as $key => $val)
			{
				if ($mid_tmp == "")
				{
					$mid_tmp = $val->module_srl . '=>"' . $val->mid . '"';
					$name_tmp = $val->module_srl . '=>"' . $val->browser_title . '"';
				}
				else
				{
					$mid_tmp = $mid_tmp . ',' . $val->module_srl . '=>"' . $val->mid . '"';
					$name_tmp = $name_tmp . ',' . $val->module_srl . '=>"' . $val->browser_title . '"';
				}
			}

			$mid_data = "<?php \$st_module_srl_to_mid = array(" . $mid_tmp . "); " . PHP_EOL . "\$st_module_srl_to_name = array(" . $name_tmp . ");?>";
			$wr_file = fopen($cache_data, "w");
			$pieces = str_split($mid_data, 1024 * 4);
			foreach ($pieces as $piece)
			{
				fwrite($wr_file, $piece, strlen($piece));
			}
			fclose($wr_file);
			@chmod($cache_data, 0707);
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

	function replaceSkinInfo($skin_info)
	{
		$st_skin_info = $skin_info;

		if (!$st_skin_info->use_page)
		{
			$st_skin_info->use_page = 'Y';
		}
		if (!$st_skin_info->list_count)
		{
			$st_skin_info->list_count = 10;
		}
		if (!$st_skin_info->page_count)
		{
			$st_skin_info->page_count = 5;
		}

		return $st_skin_info;
	}

	function convertSkinVars($skin_vars)
	{

		$ret_obj = new stdClass();
		foreach ($skin_vars as $key => $val)
		{

			$ret_obj->$key = $val->value;
		}

		return $ret_obj;
	}

	function getMemeberRecentActivity($member_info, $force_update = false)
	{
		$board_srls = null;
		$config = $this->getConfig();
		if (count($config->board_module_srls) > 0)
		{
			$board_srls = implode($config->board_module_srls, ",");
		}

		$sObj = new stdClass();
		$sObj->member_srl = $member_info->member_srl;
		$sObj->module_srl = $board_srls;
		$sObj->statusList = "PUBLIC";
		$sObj->sort_index = "regdate";
		$sObj->order_type = "desc";
		$sObj->page = 1;
		$sObj->page_count = 1;
		$sObj->list_count = 1;

		if (!$force_update)
		{
			$stOutput = executeQueryArray("something.getMemberInfo", $sObj);
		}
		else
		{
			$stOutput = new stdClass();
		}

		if ($stOutput->data)
		{
			foreach ($stOutput->data as $key => $value)
			{
				$recent_activity = $value->recent_activity;
				break;
			}
			return $recent_activity;
		}
		else
		{
			$sObj2 = new stdClass();
			$sObj2->member_srl = $member_info->member_srl;

			$oDocumentModel = getModel('document');
			$output = $oDocumentModel->getDocumentList($sObj, FALSE, TRUE);

			foreach ($output->data as $key => $value)
			{
				$output->data[$key]->regdate = $value->get('regdate');
			}
			$commentOutput = executeQueryArray("something.getCommentData", $sObj);
			if (!$commentOutput->data && !$output->data)
			{
				$sObj2->regdate = $member_info->last_login;
			}
			else
			{
				$output->data = array_merge((array)$output->data, (array)$commentOutput->data);
				usort($output->data, function($first, $second){
					return strtolower($first->regdate) < strtolower($second->regdate);
				});

				foreach ($output->data as $key => $value)
				{
					$sObj2->regdate = $value->regdate;
					break;
				}
			}

			$this->updateMemberRecentActivity($sObj2);
			return $sObj2->regdate;

		}


	}

	function updateMemberRecentActivity($obj)
	{
		$stOutput = executeQueryArray("something.getMemberInfo", $obj);
		$dbObj = new stdClass();
		$dbObj->member_srl = $obj->member_srl;
		$dbObj->recent_activity = $obj->regdate;

		if (!$stOutput->data)
		{
			$dbOutput = executeQuery("something.insertMemberRecentActivity", $dbObj);
		}
		else
		{
			$dbOutput = executeQuery("something.updateMemberRecentActivity", $dbObj);
		}

		return $dbOutput;
	}
}
/* End of file */
