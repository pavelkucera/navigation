<?php
namespace PK\Tests\Navigation;

use PK\Navigation\Node;
use PK\Navigation\NodeView;


/**
 * @author    Pavel Kučera
 */
class NodeTest extends \PHPUnit_Framework_TestCase
{
	/*******************************************************************************
	 * Children handling
	 */


	/**
	 * @expectedException \Nette\InvalidStateException
	 */
	public function testNodeCanNotBeBoundToItself()
	{
		$node = new Node();
		$node->addChild($node);
	}


	/**
	 * @expectedException \Nette\InvalidStateException
	 */
	public function testNodeCanNotBeBoundToMoreNodes()
	{
		$parent1 = new Node();
		$parent2 = new Node();
		$child = new Node();

		$parent1->addChild($child);
		$parent2->addChild($child);
	}


	public function testAddChildReturnsChildInstance()
	{
		$parent = new Node();
		$child = new Node();

		$this->assertEquals($child, $parent->addChild($child));
	}


	/*******************************************************************************
	 * Rendering
	 */


	public function testRenderReturnsViewInstance()
	{
		$node = new Node();
		$view = new NodeView();

		$this->assertEquals($view, $node->render($view));
	}


	public function testNodeRender()
	{
		$node = new Node('label', 'link');
		$view = $node->renderNode(new NodeView());

		$this->assertEquals('label', $view->label);
		$this->assertEquals('link', $view->link);
		$this->assertFalse($view->active);
	}


	public function testChildrenRender()
	{
		$parent = new Node();
			$parent->addChild(new Node('child 1'), 5);
			$parent->addChild(new Node('child 2'), 4);
			$parent->addChild(new Node('child 3'), 20);
		$view = $parent->render(new NodeView());
		$children = $view->children;

		$this->assertCount(3, $children, 'All children are rendered.');

		$this->assertEquals('child 3', array_shift($children)->label, 'Children are ordered by their descending priority');
		$this->assertEquals('child 1', array_shift($children)->label, 'Children are ordered by their descending priority');
		$this->assertEquals($view, array_shift($children)->parent, 'Parent is correctly assigned');
	}


	/*******************************************************************************
	 * Active
	 */


	public function testActivityResolveCallbackEffect()
	{
		$node = new Node('link', 'active');

		// Mark as active
		$node->resolveActive(function($link) {
			return $link === 'active';
		});
		$view = $node->render(new NodeView());
		$this->assertTrue($view->active, 'Active');

		// Mark as inactive
		$node->resolveActive(function($link) {
			return $link !== 'active';
		});
		$view = $node->render(new NodeView());
		$this->assertFalse($view->active, 'Disabled');
	}


	public function testActivityResolveCallbackResultIsNotPropagatedByDefault()
	{
		$parent = new Node('parent', 'not-active');
		$child = $parent->addChild(new Node('child', 'active'));
		$parent->resolveActive(function($link) {
			return $link === 'active';
		});

		$this->assertFalse($parent->render(new NodeView())->active);
	}


	public function testActivityResolveCallbackResultPropagation()
	{
		$parent = new Node('parent', 'not-active');
		$child = $parent->addChild(new Node('child', 'active'));
		$parent->resolveActive(function($link) {
			return $link === 'active';
		}, TRUE);

		$this->assertTrue($parent->render(new NodeView())->active);
	}


	public function testActivityResolveCallbackAppliesToWholeTree()
	{
		$parent = new Node();
			$child1 = $parent->addChild(new Node('child 1', 'not-active'));
				$nestedChild = $child1->addChild(new Node('nested child', 'active'));
			$child2 = $parent->addChild(new Node('child 2', 'active'));
		$parent->resolveActive(function($link) {
			return $link === 'active';
		}, TRUE);

		$this->assertTrue($nestedChild->render(new NodeView())->active);
		$this->assertTrue($child2->render(new NodeView())->active);
	}


	/*******************************************************************************
	 * Active nodes
	 */


	public function testRenderActiveNodes()
	{
		$parent = new Node('parent');
		$parent->addChild(new Node('child 1', 'not-active'))
			->addChild(new Node('nested child of child 1', 'active'))
				->addChild(new Node('nested child of nested child of child 1', 'not-active'));
		$parent->addChild(new Node('child 2', 'not-active'));
		$parent->resolveActive(function($link) {
			return $link === 'active';
		}, TRUE);

		$view = $parent->renderActiveNodes(new NodeView());
		$this->assertEquals('parent', $view->label, 'Parent is assigned to the base view');

		$nodes = $view->children;
		$this->assertCount(2, $nodes, 'Only active nodes are rendered');

		$this->assertEquals('child 1', array_shift($nodes)->label);
		$this->assertEquals('nested child of child 1', array_shift($nodes)->label);
	}


	public function testRenderActiveNodesWhenNone()
	{
		$parent = new Node();
		$parent->addChild(new Node('child'));

		$view = $parent->renderActiveNodes(new NodeView());
		$this->assertCount(0, $view->children);
	}


	/*******************************************************************************
	 * Restrictions
	 */


	public function testAccessRestriction()
	{
		$parent = new Node();
		$parent->addChild(new Node('restricted'))->restrictAccess('hitchhiker');
		$parent->addChild(new Node('allowed'))->restrictAccess('jedi');
		$parent->addChild(new Node('allowed'));

		$children = $parent->render(new NodeView(), function($role) {
			return $role == 'jedi';
		})->children;
		$this->assertCount(2, $children);
	}


	public function testPermissionsRestriction()
	{
		$parent = new Node();
		$parent->addChild(new Node('restricted'))->restrictPermissions('magic', 'understands');
		$parent->addChild(new Node('allowed'))->restrictPermissions('magic', 'controls');
		$parent->addChild(new Node('allowed'));

		$children = $parent->render(new NodeView(), function($resource, $privilege) {
			return $resource === 'magic' && $privilege === 'controls';
		})->children;
		$this->assertCount(2, $children);
	}


	/**
	 * @expectedException \Nette\InvalidStateException
	 */
	public function testAccessCanNotBeRestrictedAfterPermissionsRestriction()
	{
		$node = new Node();
		$node->restrictPermissions('magic', 'controls');
		$node->restrictAccess('jedi');
	}


	/**
	 * @expectedException \Nette\InvalidStateException
	 */
	public function testPermissionsCanNotBeRestrictedAfterAccessRestriction()
	{
		$node = new Node();
		$node->restrictAccess('jedi');
		$node->restrictPermissions('magic', 'controls');
	}
}
