
# Navigation
A simple tool helping you to create a structured navigation really fast.

## License
BSD 3-Clause

## Dependencies
- [the Nette Framework](http://nette.org) any version
- php >= 5.3.0

## Installation
Through composer:
```
$ composer install pavelkucera/navigation
```

## Usage

### I wanna a navigation now!
It is a way too easy to create a structured navigation.
```php
use PK\Navigation\Node;

$navigation = new Node();
$navigation->addChild(new Node('Homepage', 'Homepage:'));
$navigation->addChild(new Node('Blog:', 'Blog:'));
$portfolio = $navigation->addChild(new Node('Portfolio'));
	$portfolio->addChild(new Node('Nette', 'Portfolio:nette'));
	$portfolio->addChild(new Node('JavaScript', 'Portfolio:javascript'));
	$portfolio->addChild(new Node('Android', 'Portfolio:android'));
```
There is no limitation to the number of children.

### I need to render it
Since every site has its own way of rendering its navigation, this tool **does not** come with any “official” ```Nette\Application\UI\Control``` implementation. Nevertheless you can find an example in the examples directory.

The tool provides a view object you can easily read.
```php
use PK\Navigation\Node;
use PK\Navigation\NodeView;

$navigation = new Node('navigation');
$navigation->addChild(new Node('Homepage', 'Homepage:'));
$navigation->addChild(new Node('Blog:', 'Blog:'));

$view = $navigation->render(new NodeView());
$view->label; // 'navigation'
$view->link; // NULL
$view->active; // FALSE
$view->children; // array(2)
```

### I need to set the order
Pass the child priority as the second parameter (higher is better). In case of the same priority, time is critical (sooner is better).
```php
use PK\Navigation\Node;

$navigation = new Node();
$navigation->addChild(new Node('Homepage', 'Homepage:'), 1);
$navigation->addChild(new Node('Blog:', 'Blog:'), 5);
```

### I need to mark active node(s)
There's a method for it!
```php
use PK\Navigation\Node;

$navigation = new Node();
$navigation->addChild(new Node('Homepage', 'Homepage:'), 1);
$navigation->addChild(new Node('Blog:', 'Blog:'), 5);

$navigation->resolveActive(function($link) {
	return $presenter->isLinkCurrent($link);
});
```
It is always called upon the whole node tree.

#### When a node is active, I need its parent to be active too
Pass ```TRUE``` as the second parameter.
```php
$navigation->resolveActive($callback, TRUE);
```

#### I need to get an array of active nodes
```php
use PK\Navigation\NodeView;

$navigation->renderActiveNodes(new NodeView());
```
Method renders the node upon which the method is called to the given view object and all the active children including nested ones into the property ```$children```.

### Not everyone can see all the navigation items
You can either restrict access based on the user role or on the user permissions. Be aware, you can not combine both ways.
```php
use PK\Navigation\Node;
use PK\Navigation\NodeView;

$roleRestricted = new Node('the Jedi Temple', 'Entrance:');
$roleRestriected->restrictAccess('jedi'); // only jedi can access the jedi temple
$roleRestriected->render(new NodeView(), function($role) {
	return $role === 'jedi';
});

$permissionsRestricted = new Node('Abydos', 'Stargate:abydos');
$permissionsRestricted->restrictPermissions('startgate', 'access'); // only people with access to a stargate can travel to Abydos
$permissionsRestricted->render(new NodeView(), function($resource, $privilege) {
	return $resource === 'stargate' && $privilege === 'access';
});
```
Passing the callback to the render method is critical, it determines whether a node is rendered or not. If the callback returns ```TRUE``` the node is rendered, otherwise it is not.
