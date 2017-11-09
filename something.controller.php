<?php
class somethingController extends something
{

	function triggerBeforeModuleHandlerInit(&$oModule)
	{
		$memberStrMatch = preg_match('/^(?:disp|proc)Member/u', $oModule->act);
		if($memberStrMatch)
		{
			return;
		}
		/** @var $oModuleModel moduleModel */
		$oModuleModel = getModel('module');
		$vid = Context::get('vid');


		// Vid is not mid. so change vid to mid.
		if($vid)
		{
			// HACK(BJRambo): 강제적으로 mid를이용해서 유저 아이디를 선언
			$userId = $oModule->mid;

			$moduleInfo = $oModuleModel->getModuleInfoByMid($vid);
			if($moduleInfo->module !== 'something')
			{
				return;
			}

			$this->module = $moduleInfo->module;
			$this->mid = $moduleInfo->mid;
			$oModule->module = $moduleInfo->module;
			$oModule->mid = $moduleInfo->mid;
		}
		Context::set('user_id', $userId);
	}
}
