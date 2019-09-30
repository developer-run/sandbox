<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    UserFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Facades;

use Devrun\Doctrine\Repositories\UserRepository;
use Doctrine\ORM\Query;

class UserFacade
{

    /** @var UserRepository */
    private $userRepository;

    /**
     * UserFacade constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @return UserRepository
     */
    public function getUserRepository()
    {
        return $this->userRepository;
    }


    /**
     * @param $userName
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByLogin($userName)
    {
        return $this->userRepository->createQueryBuilder('e')
            ->where("e.username = :username")
            ->setParameter('username', $userName)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_ARRAY);
    }




}