<?php
namespace App;


/**
 * @author    Pavel Kučera
 */
class NavigationControl extends \Nette\Application\UI\Control
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
		$view = $this->tree->render(new \PK\Navigation\NodeView());
		$this->template->items = $view->children;
		$this->template->setFile(__DIR__ . '/NavigationControl.latte')->render();
	}
}
