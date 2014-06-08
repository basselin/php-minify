# PhpMinify for PHP

PhpMinify is a little tool to minify your application php like the javascript minify tools.
PhpMinify uses `php_strip_whitespace()`. The script compresses a source directory to a target directory.

## Usage

```php
require 'phpminify.php';

$phpMinify = new PhpMinify(array(
    'source' => './',
    'target' => './demo/',
));
var_dump($phpMinify->run());
```

## Options

| Key | Description | Type | Default |
|------|-------------|------|---------|
| `source` | Source directory | string | `'module/'` |
| `target` | Target directory | string | `'modulemin/'` |
| `banner` | Banner comment for each php file `<?php /* My Banner */` | string | `''` |
| `extensions` | Extensions to minify | array | `array('inc', 'php', 'phtml')` |
| `exclusions` | Extensions to be ignored during the copy | array | `array('md')` |
