<?php

/**
 * SimpleDocs
 *
 * @author Anderson Salas <anderson@ingenia.me>
 * @license MIT License
 */

namespace SimpleDocs;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use SimpleDocs\Exception\{DocumentDirectoryNotFound, FileNotFound, SimpleDocsException};

class SimpleDocs
{
    private $baseDir;

    private $filesystem;

    private $twigEnvironment;

    private $manifest = [];

    private $enableManifest = false;

    private $rebuildManifestAlways = false;


    /**
     * Class constructor
     *
     * @param  string  $baseDir Base directory
     *
     * @return mixed
     *
     * @access public
     */
    public function __construct(string $baseDir)
    {
        $this->filesystem = new Filesystem();

        if(!$this->filesystem->exists($baseDir))
        {
            throw new DocumentDirectoryNotFound('The "' . $baseDir . '" directory doesn\'t exists');
        }

        $this->baseDir = realpath($baseDir);

        $this->prepareManifest();
    }


    /**
     * Get base directory
     *
     * @return string
     *
     * @access public
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }


    /**
     * Get Twig_Environment used for template parsing
     *
     * @return mixed
     *
     * @access public
     */
    public function getTwigEnvironment()
    {
        return $this->twigEnvironment;
    }


    /**
     * Set Twig_Envioronment object used for template parsing
     *
     * @param  ?\Twig_Environment  $twig
     *
     * @return SimpleDocs
     *
     * @access public
     */
    public function setTwigEnvironment(?\Twig_Environment $twig)
    {
        $this->twigEnvironment = $twig;
        return $this;
    }


    /**
     * Parse a markdown file for special attributes
     *
     * @param  string  $path file path
     *
     * @return array
     *
     * @access public
     */
    public function parseAttributes(string $path)
    {
        $attributes = [];

        if($this->filesystem->exists($path) && substr($path,-3) == '.md')
        {
            $lines = file($path, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

            foreach($lines as $line)
            {
                $matches = [];
                if(preg_match('/^\[\/\/\]:\s#\s\((.*)\)$/', $line, $matches))
                {
                    $attribute = $matches[1];
                    if(preg_match('/^\[(.*)\](.*)$/', $attribute, $matches))
                    {
                        list(,$name,$value) = $matches;
                        $name = trim($name);
                        if(!isset($attributes[$name]))
                        {
                            $attributes[$name] = trim($value);
                        }
                    }
                }
                else
                {
                    break;
                }
            }
        }

        return $attributes;
    }


    /**
     * Parses a directory for markdown files/attributes
     *
     * @param  string  $directory
     *
     * @return void
     *
     * @access private
     */
    private function parseDirectory(string $directory = '')
    {
        $directory = rtrim($directory,'/');
        $path = $this->baseDir . '/' . $directory ;

        foreach(scandir($path) as $file)
        {
            if($file == '.' || $file == '..')
            {
                continue;
            }

            $_directory = [];

            foreach(explode('/', $directory) as $_segment)
            {
                $_directory[] = preg_match('/^[0-9]{3}_/',$_segment) ? substr($_segment, 4) : $_segment ;
            }

            $basename = trim(
                substr(  implode('/', $_directory) . '/' . (preg_match('/^[0-9]{3}_/',$file) ? substr($file, 4) : $file ),  0, -3), '/'
            );

            $fullPath = rtrim($path, '/') . '/' . $file;

            if(!is_dir($fullPath))
            {
                if(substr($file,-3) != '.md')
                {
                    continue;
                }

                $this->manifest[$basename]['attributes'] = $this->parseAttributes($fullPath);
                $this->manifest[$basename]['path'] = $fullPath;
            }
            else
            {
               $this->parseDirectory(trim($directory) . '/' . $file);
            }
        }
    }


    /**
     * Makes a manifest for current base dir
     *
     * @return void
     *
     * @access private
     */
    private function prepareManifest()
    {
        if($this->enableManifest)
        {
            $manifestPath = $this->baseDir . '/manifest.json';

            if(!$this->filesystem->exists($manifestPath) || ($this->rebuildManifestAlways && $this->filesystem->exists($manifestPath)))
            {
                $this->parseDirectory();
                $this->filesystem->dumpFile($manifestPath, json_encode($this->manifest, JSON_PRETTY_PRINT));
            }
        }
    }


    /**
     * Get current manifest file in array format, FALSE if not manifest file founded
     *
     * @return mixed
     *
     * @access public
     */
    public function getManifest()
    {
        if($this->filesystem->exists($this->baseDir . '/manifest.json'))
        {
            return json_decode(file_get_contents($this->baseDir . '/manifest.json'), true);
        }

        return false;
    }



    /**
     * Enable/disable manifest file
     *
     * @param  bool  $enable
     * @param  bool  $rebuildAlways
     *
     * @return mixed
     *
     * @access public
     */
    public function enableManifest(bool $enable = true, bool $rebuildAlways = true)
    {
        $this->enableManifest = $enable;
        $this->rebuildManifestAlways = $rebuildAlways;
        $this->prepareManifest();
    }

    /**
     * Search for a file and returns it in case of success
     *
     * @param  mixed $file file name
     *
     * @return SimpleDocsFile
     *
     * @access public
     *
     * @throws FileNotFound
     */
    public function find(string $file) : SimpleDocsFile
    {
        $path = $this->baseDir . '/' . $file . (substr($file, 0,-3) != '.md' ? '.md' : '');

        if(!$this->filesystem->exists($path))
        {
            $manifest = $this->getManifest();

            if($this->enableManifest && $manifest !== false)
            {
                if(isset($manifest[$file]))
                {
                    $path = $manifest[$file]['path'];
                }
                else
                {
                    throw new FileNotFound('The "' . $path . '" file doesn\'t exists');
                }
            }
            else
            {
                throw new FileNotFound('The "' . $path . '" file doesn\'t exists');
            }
        }

        $doc = new SimpleDocsFile($this, $path);

        return $doc;
    }
}