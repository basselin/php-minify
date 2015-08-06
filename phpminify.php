<?php
/**
 * @link      https://github.com/basselin/php-minify
 * @copyright (c) 2014, Benoit Asselin contact(at)161.io
 * @license   MIT Licence
 */

class PhpMinify
{
    /**
     * Default options
     *
     * @var array
     */
    protected $options = array(
        'source'     => 'module/', // string
        'target'     => 'modulemin/', // string
        'banner'     => '', // string
        'extensions' => array('inc', 'php', 'phtml'), // string[]
        'exclusions' => array('md'), // string[]
    );

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Source directory
     *
     * @return string
     */
    public function getSource()
    {
        return $this->fixSlashes($this->options['source']);
    }

    /**
     * Source directory
     *
     * @param  string $source
     * @return PhpMinify
     */
    public function setSource($source)
    {
        $this->options['source'] = $source;
        return $this;
    }

    /**
     * Target directory
     * @return string
     */
    public function getTarget()
    {
        return $this->fixSlashes($this->options['target']);
    }

    /**
     * Target directory
     *
     * @param  string $target
     * @return PhpMinify
     */
    public function setTarget($target)
    {
        $this->options['target'] = $target;
        return $this;
    }

    /**
     * Banner comment for each file compressed
     *
     * @return string
     */
    public function getBanner()
    {
        return $this->options['banner'];
    }

    /**
     * Banner comment for each file compressed
     *
     * @param  string $banner Eg: '/* (c) My Name *\/'
     * @return PhpMinify
     */
    public function setBanner($banner)
    {
        $this->options['banner'] = $banner;
        return $this;
    }

    /**
     * Extensions to minify
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->options['extensions'];
    }

    /**
     * Extensions to minify
     *
     * @param  array $extensions
     * @return PhpMinify
     */
    public function setExtensions(array $extensions)
    {
        $this->options['extensions'] = $extensions;
        return $this;
    }

    /**
     * Exclusions to copy
     *
     * @return array
     */
    public function getExclusions()
    {
        return $this->options['exclusions'];
    }

    /**
     * Exclusions to copy
     *
     * @param  array $extensions
     * @return PhpMinify
     */
    public function setExclusions(array $extensions)
    {
        $this->options['exclusions'] = $extensions;
        return $this;
    }

    /**
     * Minify the code
     *
     * @param  string $filename
     * @return string
     */
    public function minify($filename)
    {
        $string = php_strip_whitespace($filename);
        if ($this->getBanner()) {
            $string = preg_replace('/^<\?php/', '<?php ' . $this->getBanner(), $string);
        }
        return $string;
    }

    /**
     * For Windows
     *
     * @param  string $filename
     * @return string
     */
    public function fixSlashes($filename)
    {
        if (DIRECTORY_SEPARATOR != '/') {
            return str_replace(DIRECTORY_SEPARATOR, '/', $filename);
        }
        return $filename;
    }

    /**
     * Run the job
     *
     * @return array
     * @throws RuntimeException
     */
    public function run()
    {
        $return = array();
        $dirIterator = new \RecursiveDirectoryIterator($this->getSource());
        $iterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $key => $value) {
            if (in_array($value->getFilename(), array('..', '.DS_Store'))) { // Exclude system
                continue;
            }

            $pattern = '/^' . preg_quote($this->getSource(), '/') . '/';
            $sourcePathname = $this->fixSlashes($value->getPathname());
            $targetPathname = preg_replace($pattern, $this->getTarget(), $sourcePathname);
            if ($value->isDir()) {
                if ($value->getBasename() == '.') {
                    $dirname = dirname($targetPathname);
                    if (!is_dir($dirname)) {
                        $this->errorStart();
                        $res = mkdir($dirname, 0777, true);
                        $this->errorStop();
                        if (!$res) {
                            throw new \RuntimeException("mkdir('{$dirname}') failed", 0, $this->getLastError());
                        }
                    }
                    $return[$value->getPath()] = $dirname;
                }
                continue;
            }
            if ($value->isFile() && !in_array(strtolower($value->getExtension()), $this->getExclusions())) {
                if (in_array(strtolower($value->getExtension()), $this->getExtensions())) {
                    $this->errorStart();
                    $res = file_put_contents($targetPathname, $this->minify($sourcePathname));
                    $this->errorStop();
                    if (false === $res) {
                        throw new \RuntimeException("file_put_contents('{$targetPathname}', '...') failed", 0, $this->getLastError());
                    }
                } else {
                    $this->errorStart();
                    $res = copy($sourcePathname, $targetPathname);
                    $this->errorStop();
                    if (!$res) {
                        throw new \RuntimeException("copy('{$sourcePathname}', '{$targetPathname}') failed", 0, $this->getLastError());
                    }
                }
                $return[$sourcePathname] = $targetPathname;
            }
        } // for
        return $return;
    }



    /**
     * @var \ErrorException
     */
    protected $lastError;

    /**
     * Add an error to the stack
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @return void
     */
    public function addError($errno, $errstr = '', $errfile = '', $errline = 0)
    {
        $this->lastError = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * @return \ErrorException
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Starting the error handler
     *
     * @param int $errorLevel
     */
    public function errorStart($errorLevel = \E_WARNING)
    {
        set_error_handler(array($this, 'addError'), $errorLevel);
    }

    /**
     * Stopping the error handler
     */
    public function errorStop()
    {
        restore_error_handler();
    }
}
