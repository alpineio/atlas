# Custom Fields

Custom fields can be added to CPTs and taxonomies via doc block comments on the class. The dox blocks follow the standard
format for PHP Documented with the extra values set via JSON like WordPress's comments. The extra JSON data can be used
override any generated value.

<aside class="error">
Any error in the JSON results in a silent error and the field reverts the a standard text field. This includes having a
extra comma at the end of a property listing.
</aside>

## Basic Fields

```php
<?php
// src/Contact.php
namespace MyCRM;

use AlpineIO\Atlas\Contracts\SelfRegistration;
use AlpineIO\Atlas\Post;
/**
 * string $profileImage Profile Image
 * string $firstName First Name
 * string $lastName Last Name
 */
 class Team extends Post {
    // ...
 }
```

Adding a standard Doc Block comment describes how the data is going to be accessed from the model. The first parameter is
the type of data being returned. The second is the variable used and the third is the description. The description is
used by Piklist for the field label.

Strings are the standard return type but other types can be used as well but require using a Atlas Field interface class.

## Advanced Fields

```php
<?php
// src/Contact.php
namespace MyCRM;

use AlpineIO\Atlas\Contracts\SelfRegistration;
use AlpineIO\Atlas\Post;
use AlpineIO\Atlas\Types\PhotoFieldType;
/**
 * PhotoField $profileImage Profile Image
 * string $firstName First Name
 * string $lastName Last Name
 */
 class Team extends Post {
    // ...
    public function getProfileImageAttribute( $value ) {
        return new PhotoFieldType( $value, $this );
    }
 }
```

Advanced fields can be used to return more complex results. These advanced fields require a corresponding getter method
in the model.

## Field Types

```php
<?php
// src/Contact.php
namespace MyCRM;

use AlpineIO\Atlas\Contracts\SelfRegistration;
use AlpineIO\Atlas\Post;
/**
 * string $file Image of contact
 */
 class Team extends Post {
    // ...
 }
```

Type | Description
-----|------------
file | File Upload
string | Text Field
PhotoField | Photo helper class with extra method to make getting data about the image easier.
