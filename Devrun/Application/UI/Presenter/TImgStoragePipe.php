<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    TImgStoragePipe.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Application\UI\Presenter;

use Devrun\Storage\ImageStorage;
use Nette\Application\UI\Presenter;

trait TImgStoragePipe
{

    /** @var ImageStorage */
    public $imgStorage;

    /** @var bool is called createTemplate after inject? bug? fix this */
    private static $called = false;


    protected function attached($presenter)
    {
        if ($presenter instanceof Presenter) {
            $this->template->_imgStorage = $this->imgStorage;
        }

        parent::attached($presenter);
    }



    public function injectImgStorage(ImageStorage $imageStorage) {
        $this->imgStorage = $imageStorage;

        if (self::$called) {
            $this->template->_imgStorage = $this->imgStorage;
        }
    }

    public function createTemplate($class = null) {
        self::$called = true;

        $template = parent::createTemplate($class);
        $template->_imgStorage = $this->imgStorage;

        return $template;
    }


}