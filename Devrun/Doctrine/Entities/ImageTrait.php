<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    ImageTrait.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Doctrine\Entities;

use Devrun\CmsModule\Facades\ImageJobs\InvalidArgumentException;

/**
 * Class ImageTrait
 *
 * @package Devrun\Doctrine\Entities
 */
trait ImageTrait
{

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $referenceIdentifier;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $identifier;

    /**
     * @var string namespace
     * @ORM\Column(name="`namespace`", type="string")
     */
    protected $namespace;

    /**
     * @var string system name
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $path;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $alt;

    /**
     * @var string
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected $sha;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $width;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $height;

    /**
     * @var string
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    protected $type;



    /**
     * @return string
     */
    public function getReferenceIdentifier(): string
    {
        return $this->referenceIdentifier;
    }


    /**
     * @param string $referenceIdentifier
     *
     * @return $this
     */
    public function setReferenceIdentifier(string $referenceIdentifier)
    {
        if (!strpos($referenceIdentifier, '/')) {
            throw new InvalidArgumentException('Identifier must have two words [namespace.name]');
        }
        $this->referenceIdentifier = $referenceIdentifier;

        $pathInfo        = pathinfo($referenceIdentifier);
        $identify        = explode('/', $referenceIdentifier);
        $name            = array_pop($identify);
        $name            = $pathInfo['filename'];
        $namespace       = implode('/', $identify);
        $this->name      = $name;
        $this->namespace = $namespace;

        return $this;
    }


    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     *
     * @return $this
     */
    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlt(): string
    {
        return $this->alt;
    }

    /**
     * @param string $alt
     *
     * @return $this
     */
    public function setAlt(string $alt)
    {
        $this->alt = $alt;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return $this
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getSha(): string
    {
        return $this->sha;
    }

    /**
     * @param string $sha
     *
     * @return $this
     */
    public function setSha(string $sha)
    {
        $this->sha = $sha;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     *
     * @return $this
     */
    public function setWidth(int $width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     *
     * @return $this
     */
    public function setHeight(int $height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }










    /**
     * Utility method, that can be replaced with `::class` since php 5.5
     *
     * @return string
     */
    public static function getImageTraitName()
    {
        return get_called_class();
    }


}