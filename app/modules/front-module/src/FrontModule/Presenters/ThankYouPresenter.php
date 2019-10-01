<?php
/**
 * This file is part of nivea-2017-07-diagnostika.
 * Copyright (c) 2017
 *
 * @file    ThankYouPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\Presenters;

use FrontModule\Repositories\UserTestSiteRepository;

class ThankYouPresenter extends BaseAppPresenter
{

    /** @var UserTestSiteRepository @inject */
    public $userTestSiteRepository;


    public function actionDefault()
    {
        $section = $this->getSection('play');
        $uid = isset($section->uid) ? $section->uid : null;

        $saved = false;

        if ($uid) {
            if ($userTestSiteEntity = $this->userTestSiteRepository->findOneBy(['user.username' => $uid, 'user.active' => false])) {
                $saved = true;
            }
        }

        if (!$saved) {
//            $this->template->redirect = "Homepage:";
            $this->ajaxRedirect('Homepage:');
        }

        $this->template->saved = $saved;
    }


}