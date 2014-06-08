<?php
/**
 * @link      https://github.com/basselin/php-minify
 * @copyright (c) 2014, Benoit Asselin contact(at)ab-d.fr
 * @license   MIT Licence
 */

class PhpMinify
{
    /**
     * Default options
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
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Source directory
     * @return string
     */
    public function getSource()
    {
        return $this->options['source'];
    }
    
    /**
     * Source directory
     * @param string $source
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
        return $this->options['target'];
    }

    /**
     * Target directory
     * @param string $target
     * @return PhpMinify
     */
    public function setTarget($target)
    {
        $this->options['target'] = $target;
        return $this;
    }

    /**
     * Banner comment for each file compressed
     * @return string
     */
    public function getBanner()
    {
        return $this->options['banner'];
    }

    /**
     * Banner comment for each file compressed
     * @param string $banner Eg: '/* (c) My Name *\/'
     * @return PhpMinify
     */
    public function setBanner($banner)
    {
        $this->options['banner'] = $banner;
        return $this;
    }

    /**
     * Extensions to minify
     * @return array
     */
    public function getExtensions()
    {
        return $this->options['extensions'];
    }

    /**
     * Extensions to minify
     * @param array $extensions
     * @return PhpMinify
     */
    public function setExtensions(array $extensions)
    {
        $this->options['extensions'] = $extensions;
        return $this;
    }

    /**
     * Exclusions to copy
     * @return array
     */
    public function getExclusions()
    {
        return $this->options['exclusions'];
    }

    /**
     * Exclusions to copy
     * @param array $extensions
     * @return PhpMinify
     */
    public function setExclusions(array $extensions)
    {
        $this->options['exclusions'] = $extensions;
        return $this;
    }

    /**
     * Minify the code
     * @param string $string
     * @return string
     */
    public function minify($string)
    {
        $string = php_strip_whitespace($string);
        if ($this->getBanner()) {
            $string = preg_replace('/^<\?php/', '<?php ' . $this->getBanner(), $string);
        }
        return $string;
    }

    /**
     * Run the job
     * @return array
     */
    public function run()
    {
        $return = array();
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getSource()), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $key => $value) {
            if (in_array($value->getFilename(), array('..', '.DS_Store'))) { // Exclude system
                continue;
            }

            $targetPathname = preg_replace('/^' . preg_quote($this->getSource(), '/') . '/', $this->getTarget(), $value->getPathname());
            if ($value->isDir()) {
                if ($value->getBasename() == '.') {
                    $dirname = dirname($targetPathname);
                    if (!is_dir($dirname)) {
                        mkdir($dirname);
                    }
                    $return[$value->getPath()] = $dirname;
                }
                continue;
            }
            if ($value->isFile() && !in_array(strtolower($value->getExtension()), $this->getExclusions())) {
                if (in_array(strtolower($value->getExtension()), $this->getExtensions())) {
                    file_put_contents($targetPathname, $this->minify($value->getPathname()));
                } else {
                    copy($value->getPathname(), $targetPathname);
                }
                $return[$value->getPathname()] = $targetPathname;
            }
        } // for
        return $return;
    }
}
