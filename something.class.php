<?php
class something extends ModuleObject
{
	private $trigger = array(
		array('moduleHandler.init', 'something', 'controller', 'triggerBeforeModuleHandlerInit', 'before'),
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


		return new Object();
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
}
/* End of file */
