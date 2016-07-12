<?php

namespace Tulinkry;

use Nextras\Datagrid\Datagrid;
use Nette\Templating\IFileTemplate;


class Grid extends Datagrid
{


	public function render()
	{
		if ($this->filterFormFactory) {
			$this['form']['filter']->setDefaults($this->filter);
		}

		$this->template->data = $this->getData();
		$this->template->columns = $this->columns;
		$this->template->editRowKey = $this->editRowKey;
		$this->template->rowPrimaryKey = $this->rowPrimaryKey;
		$this->template->paginator = $this->paginator;

		foreach ($this->cellsTemplates as &$cellsTemplate) {
			if ($cellsTemplate instanceof IFileTemplate) {
				$cellsTemplate = $cellsTemplate->getFile();
			}
			if (!file_exists($cellsTemplate)) {
				throw new \RuntimeException("Cells template '{$cellsTemplate}' does not exist.");
			}
		}

		$this->template->cellsTemplates = $this->cellsTemplates;
		$this->template->showFilterCancel = $this->filterDataSource != $this->filterDefaults; // @ intentionaly


		$args = func_get_args ();
		if ( count ( $args ) > 0 )
		{
			if ( __DIR__ . "/" . $args [ 0 ] )
				$this -> template -> setFile ( __DIR__ . "/templates/" . $args [ 0 ] );
			array_shift ( $args );
			foreach ( $args as $key => $arg )
				if ( file_exists( __DIR__ . "/" . $arg ) )
					$this -> addCellsTemplate ( __DIR__ . "/templates/" . $arg );
		}
		else
			$this->template->setFile(__DIR__ . '/templates/Bootstrap.latte');

		$this->template->render();
	}


}