<?php
class somethingController extends something
{
	function triggerBeforeModuleHandlerInit(&$oModule)
	{
		/** @var $oModuleModel moduleModel */
		$oModuleModel = getModel('module');
		$vid = Context::get('vid');
		// HACK(BJRambo): 강제적으로 mid를이용해서 유저 아이디를 선언
		$userId = $oModule->mid;
		Context::set('user_id', $userId);

		// Vid is not mid. so change vid to mid.
		if($vid)
		{
			$moduleInfo = $oModuleModel->getModuleInfoByMid($vid);

			$this->module = $moduleInfo->module;
			$this->mid = $moduleInfo->mid;
			$oModule->module = $moduleInfo->module;
			$oModule->mid = $moduleInfo->mid;
		}
	}
}
