<?php
namespace PK\Navigation;


/**
 * @author	Pavel Kučera
 */
class Node extends \Nette\Object
{
	/** @var string */
	private $label;

	/** @var string */
	private $link;

	/** @var bool */
	private $active;

	/** @var string */
	private $role;

	/** @var string */
	private $resource;

	/** @var string */
	private $privilege;

	/** @var Node */
	private $parent;

	/** @var Node[] */
	private $children;

	/** @var int */
	private $nextChildrenNumber = PHP_INT_MAX;


	/**
	 * @param string
	 * @param string
	 */
	public function __construct($label = NULL, $link = NULL)
	{
		$this->active = FALSE;
		$this->children = new \SplPriorityQueue();

		$this->label = $label;
		$this->link = $link;
	}


	/**
	 * @param Node
	 * @param [int]
	 * @return Node the given child
	 */
	public function addChild(Node $node, $priority = NULL)
	{
		if ($node->parent) {
			throw new \Nette\InvalidStateException("The node is already bound to a parent.");
		}
		if ($node === $this) {
			throw new \Nette\InvalidStateException("The node can not be bound to itself.");
		}
		$node->parent = $this;
		$this->children->insert($node, array($priority, $this->nextChildrenNumber--));
		return $node;
	}


	/**
	 * @param callable
	 * @param bool
	 * @return void
	 */
	public function resolveActive($isLinkActiveCallback, $propagate = FALSE)
	{
		$this->active = (bool) $isLinkActiveCallback($this->link);
		foreach (clone $this->children as $child) {
			$child->resolveActive($isLinkActiveCallback, $propagate);
		}
		if ($propagate && $this->active && $this->parent) {
			$this->parent->active = $this->active;
		}
	}


	/**
	 * @param string
	 * @return void
	 */
	public function restrictAccess($role)
	{
		if ($this->resource) {
			throw new \Nette\InvalidStateException("Access can not be restricted when permissions have been already restricted.");
		}
		$this->role = $role;
	}


	/**
	 * @param [string]
	 * @param [string]
	 * @return void
	 */
	public function restrictPermissions($resource, $privilege = NULL)
	{
		if ($this->role) {
			throw new \Nette\InvalidStateException("Permissions can not be restricted when access has been already restricted.");
		}
		$this->resource = $resource;
		$this->privilege = $privilege;
	}


	/**
	 * @param [callable]
	 * @return bool
	 */
	protected function isAuthorized($callback)
	{
		if (!$callback || (!$this->role && !$this->resource)) {
			return TRUE;
		}
		if ($this->role) {
			return $callback($this->role);
		}
		return $callback($this->resource, $this->privilege);
	}


	/**
	 * @param BaseNodeView
	 * @param [callable]
	 * @return NodeView|NULL
	 */
	public function renderNode(NodeView $view, $authorizationCallback = NULL)
	{
		if (!$this->isAuthorized($authorizationCallback)) {
			return NULL;
		}
		$view->label = $this->label;
		$view->link = $this->link;
		$view->active = $this->active;
		return $view;
	}


	/**
	 * @param NodeView
	 * @param [callable]
	 * @return NodeView|NULL
	 */
	public function render(NodeView $view, $authorizationCallback = NULL)
	{
		if (!$this->renderNode($view, $authorizationCallback)) {
			return NULL;
		}
		$children = clone $this->children;
		foreach ($children as $child) {
			$childView = $child->render(new $view, $authorizationCallback);
			if (!$childView) {
				continue;
			}
			$childView->parent = $view;
			$view->children[] = $childView;
		}
		return $view;
	}


	/**
	 * @param NodeView
	 * @return NodeView
	 */
	public function renderActiveNodes(NodeView $view)
	{
		if (!$this->active) {
			return $view;
		}
		foreach (clone $this->children as $child) {
			if (!$child->active) {
				continue;
			}
			$view->children[] = $child->renderNode(new $view);
			$child->renderActiveNodes($view);
			break;
		}
		$this->renderNode($view);
		return $view;
	}
}
