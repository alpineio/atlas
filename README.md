# atlas

A Custom Post Type manager for WordPress.  Extends Piklist and native WordPress functionality.

## Synopsis

Piklist does a great job of adding datastructure to WordPress.  About Piklist:

"Piklist is a code-based framework, which means it has no user interface. We believe this is one of the biggest 
benefits to using Piklist. Code based systems allow you flexibility in your field configurations and development, 
and will save you time in the long run. With Piklist you can usually use the same code you have written in one 
section and use it in another, allowing you to copy and paste, and save tons of time."

We agree with the benefits of a code-based, source-controlled application.   But, we feel that copy-paste is not 
a sustainable model for large-scale application development.  

Atlas is a layer over the copy/paste configuration model.   A convenient, object-oriented layer for adding 
basic and custom functionality to extended post types using a style similar to Laravel.  

As they say about Laravel, it is "an accessible, yet powerful system, providing tools needed for large, robust 
applications".  We envision the same for Atlas in the WordPress ecosystem.

## Code Example

Here is an example of an object being defined with a piklist-compatible data dictionary, and behavior inherited from posts.

```
namespace MyCRM;

use AlpineIO\Atlas\Contracts\SelfRegistration;
use AlpineIO\Atlas\Post;
use AlpineIO\Atlas\Traits\PiklistPostRegistration;
use AlpineIO\Atlas\Types\PostRelationFieldType;

/**
 * Class Contact
 * @package MyCRM
 * @property string $title Job Title
 * @property string $street_address Street Address
 * @property string $city City
 * @property string $state State
 * @property string $website Website URL
 * @property string $facebook Facebook URL
 * @property string $yelp Yelp URL
 */
class Contact extends Post implements SelfRegistration {

	use PiklistPostRegistration;

}
```

Next, we might use this CPT Contact within a WordPress single template:

```
  use MyCRM\Contact;

  $contact = new Contact(get_the_ID());
```

All of our contact attributes and member functionality are available within this object for quick access and 
display in your WordPress template.

### Building the data model with PikList

First, build your administrative interface with PikList:

  https://piklist.com/user-guide/tutorials/getting-started-with-piklist/

then, extend it using Atlas.

## Motivation

We love the spirit behind the PikList project.  Though, we felt that to maintain large scale applications, we would need a
more modern object structure.  As users of Laravel, we missed the elegance and ease of use and felt we could help.

## Installation

Install using composer as in the following:

```
{
  "name": "alpine/MyCRM",
  "description": "MyCRM App",
  "minimum-stability": "stable",
  "license": "proprietary",
  "authors": [
    {
      "name": "Morgan O'Neal",
      "email": "moneal@alpine.io"
    }
  ],
  "autoload": {
    "psr-4": {
      "SFF\\": "src/"
    }
  },
  "require": {
    "alpineio/atlas": "^1.0"
  },
  "require-dev": {
    "symfony/var-dumper": "^3.0"
  }
}
```

## Tests

Coming soon.

## Contributors

We would welcome additional help on this project.  Please feel free to fork and send pull requests.

## License

GPL2
