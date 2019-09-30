<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    WebLoaderJsControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Controls;

interface IWebLoaderJsControl
{
    /** @return WebLoaderJsControl */
    function create();
}
use WebLoader;
use WebLoader\Nette\JavaScriptLoader;

class WebLoaderJsControl extends JavaScriptLoader
{
    /** @var string */
    private $tempDir = '';

    /** @var string */
    private $wwwDir = '';

    private $webLoaderCollections = [];


    public function _render($params = null)
    {

//        dump($params);

        dump($this->webLoaderCollections);
//        die();
        $outputDir = $this->webLoaderCollections['outputDir'];

        $cssFiles = $this->webLoaderCollections['css']['cms']['files'];
        $jsFiles = $this->webLoaderCollections['js']['cms']['files'];

//        dump($this->context);
//        die();

        $files = new WebLoader\FileCollection();
        $files->addFiles($cssFiles);

//        $files->addWatchFiles(Nette\Utils\Finder::findFiles('*.css', '*.less')->in(WWW_DIR . '/css'));

        $compiler = WebLoader\Compiler::createJsCompiler($files, $outputDir);

//        $compiler->addFilter(new WebLoader\Filter\VariablesFilter(array('foo' => 'bar')));
//        $compiler->addFilter(function ($code) {
//            return \CssMin::minify($code, "remove-last-semicolon");
//        });

        $control = new JavaScriptLoader($compiler, '/webTemp');
//        $control->setMedia('screen');

        $control->render();

//        return $control;
    }

    /**
     * @param string $tempDir
     */
    public function setTempDir($tempDir)
    {
        $this->tempDir = $tempDir;
    }

    /**
     * @param string $wwwDir
     */
    public function setWwwDir($wwwDir)
    {
        $this->wwwDir = $wwwDir;
    }

    /**
     * @param array $webLoaderCollections
     */
    public function setWebLoaderCollections($webLoaderCollections)
    {
        $this->webLoaderCollections = $webLoaderCollections;
    }

}