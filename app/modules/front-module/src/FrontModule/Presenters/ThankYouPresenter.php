<?php
/**
 * This file is part of nivea-2017-07-diagnostika.
 * Copyright (c) 2017
 *
 * @file    ThankYouPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace FrontModule\Presenters;


class ThankYouPresenter extends BaseAppPresenter
{


    public function actionDefault()
    {
        $section = $this->getSection('play');
        $uid = isset($section->uid) ? $section->uid : null;

        $saved = false;

        if ($uid) {

        }

        if (!$saved) {
//            $this->template->redirect = "Homepage:";
//            $this->ajaxRedirect('Homepage:');
        }


        $this->template->saved = $saved;
    }


}