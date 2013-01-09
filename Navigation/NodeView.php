<?php
namespace PK\Navigation;


/**
 * @author    Pavel Kučera
 */
class NodeView extends \Nette\Object
{
	/** @var string */
	public $label;

	/** @var string */
	public $link;

	/** @var bool */
	public $active = FALSE;

	/** @var NodeView */
	public $parent;

	/** @var array */
	public $children = array();
}
