# Models

Atlas uses models modeled after Laravel's excellent model class.

## Creating

Models are simple classes to help us get and set data for our post typesa.

Models should be placed in place accessible by composers autoloader.

<aside class="notice">
In our examples our classes are loaded form <code>/src</code> folder using <a href="http://www.php-fig.org/psr/psr-4/">PSR-4 Autoloading</a>
</aside>

## Custom Post Types

```php
<?php
// src/Contact.php
namespace MyCRM;

use AlpineIO\Atlas\Contracts\SelfRegistration;
use AlpineIO\Atlas\Post;

/**
 * Class Contact
 */
class Contact extends Post implements SelfRegistration {

	use PiklistPostRegistration;

}
```

This is a example creating a CPT called Contact. Atlas takes sets all the defaults from this including labels and slugs.

<aside class="notice">
All CPT and taxonomy classes should be singular names. Atlas assumes this and sets the plurar variations for us. If needed this can
over written.
</aside>

## Taxonomies

```php
<?php
// src/Team.php
namespace ACME\MyCRM;
use AlpineIO\Atlas\Abstracts\AbstractTaxonomy;

/**
 * Class Team
 */
class Team extends AbstractTaxonomy {
	protected static $postTypes = [Team::class];
}
```

Creating a custom taxonomy is ver similar to creating a CPT only you must add an array of all the post type classes.

*[CPT]: Custom Post Type

