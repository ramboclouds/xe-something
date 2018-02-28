<?php
class something extends ModuleObject
{
	private $trigger = array(
		array('moduleHandler.init', 'something', 'controller', 'triggerAddMemberMenu', 'after'),
	);

	function moduleInstall()
	{
		/** @var $oModuleController moduleController */
		$oModuleController = getController('module');
		/** @var $oModuleModel moduleModel */
		$oModuleModel = getModel('module');

		foreach ($this->trigger as $trigger)
		{
			if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				$output = $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
				if(!$output->toBool())
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
			if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
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
			if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				$output = $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
				if(!$output->toBool())
				{
					return $output;
				}
			}
		}

		return new Object();
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
