<?php

namespace FrontModule\Presenters;

use Devrun\Application\UI\Presenter\BasePresenter;
use Devrun\Application\UI\Presenter\TImgStoragePipe;
use Devrun\CmsModule\Utils\Common;
use FrontModule\Filters\CommonFilter;

/**
 * Base presenter for all application presenters.
 */
class BaseAppPresenter extends BasePresenter
{
    const SESSION_SECTION = 'code';

//    use TArticlesPipe;
    use TImgStoragePipe;


    /** @var CommonFilter @inject */
    public $commonFilter;





    protected function beforeRender()
    {
        parent::beforeRender();

        $cmsClass = Common::isAdminRequest() ? ' in' : null;

        $this->template->production = $this->context->parameters['productionMode'];
        $this->template->pageClass = trim("main-wrapper ajax-fade {$this->template->pageClass}{$cmsClass}");
        $this->template->locale = $this->translator->getLocale();
//        $this->template->analyticCode = 'UA-113969633-2';
    }




    /**
     * @return \FrontModule\Control\FlashMessageControl
     */
    protected function createComponentFlashMessage()
    {
        return $this->flashMessageControl->create();
    }



    public function flashMessage($message, $type = 'info', $title = '', array $options = array())
    {
        $id         = $this->getParameterId('flash');
        $messages   = $this->getPresenter()->getFlashSession()->$id;
        $messages[] = $flash = (object)array(
            'message' => $message,
            'title'   => $title,
            'type'    => $type,
            'options' => $options,
        );

        $this->getTemplate()->flashes = $messages;
        $this->getPresenter()->getFlashSession()->$id = $messages;
        return $flash;
    }



    public function getMySession()
    {
        $return = $this->getSession()->setName('pf_nivea')->getSection(self::SESSION_SECTION);
        return $return;
    }


    public function getSection($name = 'play')
    {
        return $this->getSession($name);
    }

}
