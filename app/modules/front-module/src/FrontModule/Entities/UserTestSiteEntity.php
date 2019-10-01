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
use Devrun\Doctrine\Entities\UserEntity;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class TestSitesEntity
 * @ORM\Entity(repositoryClass="FrontModule\Repositories\UserTestSiteRepository")
 * @ORM\Table(name="user_test_site")
 *
 */
class UserTestSiteEntity
{

    use IdentifiedEntityTrait;
    use DateTimeTrait;
    use BlameableTrait;
    use MagicAccessors;

    /**
     * @var UserEntity
     * @ORM\ManyToOne(targetEntity="Devrun\Doctrine\Entities\UserEntity", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var TestSitesEntity
     * @ORM\ManyToOne(targetEntity="TestSitesEntity", inversedBy="userTestSite")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $testSite;

    /**
     * UserTestSiteEntity constructor.
     * @param UserEntity $user
     */
    public function __construct(UserEntity $user = null)
    {
        $this->user = $user;
    }


    /**
     * @return UserEntity
     */
    public function getUser(): UserEntity
    {
        return $this->user;
    }

    /**
     * @param UserEntity $user
     * @return UserTestSiteEntity
     */
    public function setUser(UserEntity $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return TestSitesEntity|null
     */
    public function getTestSite()
    {
        return $this->testSite;
    }

    /**
     * @param TestSitesEntity $testSite
     * @return UserTestSiteEntity
     */
    public function setTestSite(TestSitesEntity $testSite)
    {
        $this->testSite = $testSite;
        return $this;
    }



}