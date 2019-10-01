<?php
/**
 * This file is part of q7.audi.cz.
 * Copyright (c) 2019
 *
 * @file    TestSitesEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\Entities;

use Devrun\Doctrine\Entities\BlameableTrait;
use Devrun\Doctrine\Entities\DateTimeTrait;
use Devrun\Doctrine\Entities\IdentifiedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class TestSitesEntity
 * @ORM\Entity(repositoryClass="FrontModule\Repositories\TestSiteRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 * @ORM\Table(name="test_sites")
 *
 * @method getName()
 */
class TestSitesEntity
{

    use IdentifiedEntityTrait;
    use DateTimeTrait;
    use BlameableTrait;
    use MagicAccessors;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    protected $name;


    /**
     * @var UserTestSiteEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="UserTestSiteEntity", mappedBy="testSite")
     */
    protected $userTestSite;



    /**
     * BranchesEntity constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->userTestSite = new ArrayCollection();
    }


    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }





}