<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    WebLoaderCssControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Controls;

interface IWebLoaderCssControl
{
    /** @return WebLoaderCssControl */
    function create();
}

use WebLoader;
use WebLoader\Nette\CssLoader;

class WebLoaderCssControl extends CssLoader
{
    /** @var string */
    private $tempDir = '';

    /** @var string */
    private $wwwDir = '';

    private $webLoaderCollections = [];


    public function render($params = null)
    {
        /*
         * @todo params zpracujeme zvlášť, po zapracování zavoláme parent bez parametrů
         */

        parent::render();
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