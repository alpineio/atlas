# Installation

Installing Atlas is really easy with composer and currently the only way we
recommend doing it.

## Requirements

Atlas requires WordPress 4.5 and [Piklist](https://piklist.com/) 9.9.8 for basic functionality.

## Composer


```shell
$ composer require alpineio/atlas
```

 > Your projects composer `composer.json` will be updated to include atlas.

```json
{
  "name": "ACME/MyCRM",
  "autoload": {
    "psr-4": {
      "ACME\\MyCRM\\": "src/"
    }
  },
  "require": {
    "alpineio/atlas": "~1.0"
  }
}
```
> `ACME` and `MyCRM` would be replaced with your specific namespace and project.

User the composer command line to install the most recent composer package.

## WordPress

```php
<?php
require 'vendor/autoload.php';
use AlpineIO\Atlas\Services\RegisterWordPressTypes;
use MyCRM\Contact;
use MyCRM\Team;

// Create a filter to return the classes
function my_crm_object_types( $types = [] ) {
	return array_merge( $types, [
		Contact::class,
		Team::class
	] );
}
add_filter('alpineio_atlas_models', 'my_crm_object_types');

RegisterWordPressTypes::register();
```

You can add the composer autoloader to `functions.php` or your theme in any file.

The [RegisterWordPressTypes](https://github.com/alpineio/atlas/blob/master/src/Services/RegisterWordPressTypes.php) class uses a filter called `alpineio_atlas_models` to get a list of classes to add.
These classes should implement the [SelfRegistration](https://github.com/alpineio/atlas/blob/master/src/Contracts/SelfRegistration.php) interface.

<aside class="notice">
You if loading in a hook it should be done before the <strong>????</strong> hook.
</aside>
