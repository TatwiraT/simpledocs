<p align="center">
    <img src="https://ingenia.me/images/simpledocs-logo.png" width="100" />
</p>

**SimpleDocs** is a dynamic documentation library for PHP which uses Markdown files. _Project under development, more documentation coming soon_

### Features

* Dynamic routing
* Markdown file attributes: strings that can be fetched from documents
* Twig template post-processing (optional)

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

## Donate

If you love our work, you can support us via [Paypal](https://paypal.me/andersalasm) or [Patreon](https://patreon.com/ingeniasoftware) 

