<?php

class something extends ModuleObject
{
	private $trigger = array(
		array('moduleHandler.init', 'something', 'controller', 'triggerAddMemberMenu', 'after'),
		array('document.insertDocument', 'something', 'controller', 'triggerInsertAfterDocument', 'after'),
		array('comment.insertComment', 'something', 'controller', 'triggerInsertAfterComment', 'after'),
		array('document.deleteDocument', 'something', 'controller', 'triggerDeleteAfterDocument', 'after'),
		array('comment.deleteComment', 'something', 'controller', 'triggerDeleteAfterComment', 'after'),
	);

	protected  static $config = null;

	protected function getConfig()
	{
		if (self::$config === null)
		{
			/** @var $oModuleModel moduleModel */
			$oModuleModel = getModel('module');
			$config = $oModuleModel->getModuleConfig($this->module) ?: new stdClass;

			if (!$config)
			{
				$config = new stdClass();
			}

			if (!$config->use)
			{
				$config->use = 'N';
			}

			if (!$config->group)
			{
				$config->group = 'all';
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

			if (!$config->subscribe_use)
			{
				$config->subscribe_use = 'Y';
			}

			if (!$config->subscribe_click_action)
			{
				$config->subscribe_click_action = 'list';
			}

			if (!$config->subscribe_follow_view_use)
			{
				$config->subscribe_follow_view_use = 'Y';
			}

			if (!$config->subscribe_follow_view_menu_name)
			{
				$config->subscribe_follow_view_menu_name = '팔로우 글';
			}

			self::$config = $config;
		}

		return self::$config;
	}

	function moduleInstall()
	{
		/** @var $oModuleController moduleController */
		$oModuleController = getController('module');
		/** @var $oModuleModel moduleModel */
		$oModuleModel = getModel('module');

		foreach ($this->trigger as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				$output = $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
				if (!$output->toBool())
				{
					return $output;
				}
			}
		}
		return $this->makeObject();
	}

	function checkUpdate()
	{
		$oModuleModel = getModel('module');
		foreach ($this->trigger as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				return true;
			}
		}

		return false;
	}

	function moduleUpdate()
	{
		$oModuleController = getController('module');
		$oModuleModel = getModel('module');

		foreach ($this->trigger as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				$output = $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
				if (!$output->toBool())
				{
					return $output;
				}
			}
		}

		return $this->makeObject();
	}

	/**
	 * Create new Object for php7.2
	 * @param int $code
	 * @param string $msg
	 * @return BaseObject|Object
	 */
	public function makeObject($code = 0, $msg = 'success')
	{
		return class_exists('BaseObject') ? new BaseObject($code, $msg) : new Object($code, $msg);
	}
}
/* End of file */
