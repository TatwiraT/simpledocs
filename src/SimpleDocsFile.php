<?php

/**
 * SimpleDocsFile
 *
 * @author Anderson Salas <anderson@ingenia.me>
 * @license MIT License
 */

namespace SimpleDocs;

class SimpleDocsFile
{
    private $instance;

    private $filePath;

    private $attributes = [];

    private $content;

    private $output;

    private $parsedown;

    private $template;

    private $lastModified;

    private $sections;

    private $title;

    /**
     * Class constructor
     *
     * @param  mixed  $path Absolute file path
     *
     * @return void
     *
     * @access public
     */
    public function __construct(SimpleDocs $instance, String $path)
    {
        $this->instance     = $instance;
        $this->filePath     = $path;
        $this->content      = file_get_contents($path);
        $this->parsedown    = new \Parsedown();
        $this->lastModified = (int) filemtime($path);
        $this->attributes   = $instance->parseAttributes($path);
        $this->sections     = $instance->parseSections($path);
        $this->title        = $instance->getTitle();
        $this->ouput        = $this->parsedown->text($this->content);
    }


    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getLastModified()
    {
        return $this->lastModified;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function render(array $vars = [], bool $return = false)
    {
        $twig = $this->instance->getTwigEnvironment();

        if(!empty($twig))
        {
            $this->template = $twig->createTemplate($this->ouput);
        }
        else
        {
            if(!empty($vars))
            {
                throw new SimpleDocsException("You must provide a Twig_Environment object in order to use template pre-processing");
            }
        }

        if(empty($twig))
        {
            if(!$return)
            {
                echo $this->ouput;
            }

            return $this->ouput;
        }

        return $this->template->{ $return ? 'render' : 'display' }(array_merge($this->attributes, $vars));
    }


    public function toArray(array $vars = [])
    {
        $output =  $this->render($vars, true);

        return [
            'title'      => $this->title,
            'sections'   => $this->sections,
            'attributes' => $this->attributes,
            'output'     => $output,
            'document'   => $this,
        ];
    }
}