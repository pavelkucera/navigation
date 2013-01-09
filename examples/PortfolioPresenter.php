<?php

use PK\Navigation\Node;


/**
 * Portfolio presenter.
 */
class PortfolioPresenter extends BasePresenter
{
	/** @var \PK\Navigation\Node */
	private $navigation;


	protected function startup()
	{
		parent::startup();

		$nav = $this->navigation = new Node('Portfolio', 'Portfolio:');
		$nav->addChild(new Node('Me', 'Portfolio:me'));
		$nette = $nav->addChild(new Node('Nette'));
			$nette->addChild(new Node('Controls', 'Portfolio:controls'));
			$nette->addChild(new Node('Latte', 'Portfolio:latte'));
		$nav->addChild(new Node('node.js', 'Portfolio:nodejs'));

		$nav->resolveActive(function($link) {
			return $link && $this->isLinkCurrent($link);
		}, TRUE);
	}


	protected function createComponentNavigation()
	{
		return new \App\NavigationControl($this->navigation);
	}


	protected function createComponentBreadcrumbs()
	{
		return new \App\BreadcrumbControl($this->navigation);
	}
}
