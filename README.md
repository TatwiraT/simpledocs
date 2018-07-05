<p align="center">
    <img src="https://ingenia.me/images/simpledocs.png" width="100" />
</p>

<p align="center"><strong>WARNING: Under development!</strong></p>

**SimpleDocs** is a documentation library for PHP which uses Markdown files.

### Features

* Dynamic routing: all docs are parsed on-the-fly and served as HTML
* Markdown file attributes: special attributes that can be parsed from documents

### Requirements

* PHP >= 7.0.0

### Installation

SimpleDocs is available with Composer:

```
composer require simpledocs/simpledocs
```

### Usage

You must provide a directory path for search and retrieve documents during the library initialization:

```php
<?php

// If you aren't using any framework, make sure that the Composer autoload
// file is included in the script:
// require __DIR__ . '/vendor/autoload.php';

use SimpleDocs\SimpledDocs;
use SimpleDocs\Exception\FileNotFound;

$docs = new SimpleDocs('path/to/your/docs');

// The query string can be any string. For example purposes we use a GET
// variable:
$path = $_GET['path'] ?? '/';

try
{
    $page = $docs->find($path);
}
catch(FileNotFound $e)
{
    // Here we handle the Not Found exception. Again, for example purposes
    // a die() function is used, but a better approach is to show a 404 page or
    // or something more informative to the user
    die('Document not found!');
}

// And finally, render the result:
$page->render();
```

### Documentation

Coming soon!

### Related projects

* [Luthier CI](https://github.com/ingeniasoftware/luthier-ci): Improved routing, middleware support, authentication tools and more for CodeIgniter 3 framework
* [Luthier Framework](https://github.com/ingeniasoftware/luthier-framework): Versatile PHP micro-framework for build APIs and websites quickly

### Donate

If you love our work,  consider support us on [Patreon](https://patreon.com/ingenia)
