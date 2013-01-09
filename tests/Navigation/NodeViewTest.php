<?php
namespace PK\Tests\Navigation;


/**
 * @author	Pavel Kučera
 */
class NodeViewTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PK\Navigation\NodeView */
	private $view;


	protected function setup()
	{
		$this->view = new \PK\Navigation\NodeView();
	}


	public function testLabel()
	{
		$this->view->label = 'label';
		$this->assertEquals('label', $this->view->label);
	}


	public function testLink()
	{
		$this->view->link = 'link';
		$this->assertEquals('link', $this->view->link);
	}


	public function testActive()
	{
		$this->view->active = TRUE;
		$this->assertEquals(TRUE, $this->view->active);
	}


	public function testParent()
	{
		$parent = new \PK\Navigation\NodeView();
		$this->view->parent = $parent;
		$this->assertEquals($parent, $this->view->parent);
	}


	public function testChildren()
	{
		$child = new \PK\Navigation\NodeView();
		$this->view->children[] = $child;
		$this->assertEquals($child, reset($this->view->children));
	}
}
