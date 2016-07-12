<?php

namespace Tulinkry\Application\UI;

use Nette;

abstract class Control extends Nette\Application\UI\Control
{


	/**	 
	 * Create template and autoset file
	 * @param string
	 * @param bool
	 */
	public function createTemplate($class = NULL, $autosetFile = TRUE)
	{
		$template = parent::createTemplate($class);
		//$this->getService("template")->configure($template);

		if ($autosetFile && !$template->getFile() && file_exists($this->getTemplateFilePath())) {
			$template->setFile($this->getTemplateFilePath());
		}

		return $template;
	}


	/** 
	 * Sets up template
	 * @param string
	 */
	public function useTemplate($name = NULL)
	{
		$this->template->setFile($this->getTemplateFilePath($name));
	}


	/**
	 * Create template from file
	 * @param string
	 */	
	public function createTemplateFromFile($file)
	{	
		$template = $this->createTemplate(NULL, FALSE);
		$template->setFile($file);

		return $template;
	}


	/**
	 * Derives template path from class name
	 * @param string
	 * @return string
	 */
	protected function getTemplateFilePath($name = "")
	{
		$class = $this->getReflection();
		return dirname($class->getFileName()) . "/" . $class->getShortName() . ucfirst($name) . ".latte";
	}


	/**
	 * Renders the default template
	 */
	public function render()
	{
		$this->template->render();
	}


	public function isAjax ()
	{
		return $this -> presenter -> isAjax ();
	}

	/********************* components *********************/


	/**
	 * FlashMessage component
	 * @return Components\FlashMessageControl
	 */
	//protected function createComponentFlashMessage()
	//{
	//	return new FlashMessageControl;
	//}




}