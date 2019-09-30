<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    PublicPagesResult.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\DependencyResolver;


class PublicPagesResult
{
    private $scanDirectories = [];

    private $pagesTimes = [];

    /**
     * @return array
     */
    public function getScanDirectories()
    {
        return $this->scanDirectories;
    }

    /**
     * @param array $scanDirectories
     *
     * @return $this
     */
    public function setScanDirectories($scanDirectories)
    {
        $this->scanDirectories = $scanDirectories;
        return $this;
    }




    /**
     * @return array
     */
    public function getPageTimes()
    {
        return $this->pagesTimes;
    }

    /**
     * @param array $pages
     *
     * @return $this
     */
    public function setPageTimes($pages)
    {
        $this->pagesTimes = $pages;
        return $this;
    }

    public function getPages()
    {
        return array_keys($this->scanDirectories);
    }






}