<?php
class somethingAdminView extends something
{
	function init()
	{
		$this->setTemplatePath($this->module_path . 'tpl');
		$this->setTemplateFile(str_replace('dispSomethingAdmin', '', $this->act));
	}
}
/* End of file */
