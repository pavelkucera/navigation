<?php
namespace App;


/**
 * @author    Pavel Kučera
 */
class BreadcrumbControl extends \Nette\Application\UI\Control
{
	/** @var \PK\Navigation\Node */
	private $tree;


	/**
	 * @param PK\Navigation\Node
	 */
	public function __construct(\PK\Navigation\Node $tree)
	{
		parent::__construct();
		$this->tree = $tree;
	}


	public function render()
	{
		$view = $this->tree->renderActiveNodes(new \PK\Navigation\NodeView());
		$this->template->breadcrumbs = $view->children;
		$this->template->setFile(__DIR__ . '/BreadcrumbControl.latte')->render();
	}
}
