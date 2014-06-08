<?php
/**
 * @link      https://github.com/basselin/php-minify
 * @copyright (c) 2014, Benoit Asselin contact(at)ab-d.fr
 * @license   MIT Licence
 */

require 'phpminify.php';

$phpMinify = new PhpMinify(array(
    'source' => './',
    'target' => './demo/',
));
var_dump($phpMinify->run());
