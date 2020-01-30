<?php

namespace FrontModule\Presenters;

use Devrun\Doctrine\Entities\UserEntity;
use Devrun\Doctrine\Repositories\UserRepository;
use FrontModule\Forms\ISecureFormFactory;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * Class HomepagePresenter
 * @package FrontModule\Presenters
 */
class HomepagePresenter extends BaseAppPresenter
{

    /** @var ISecureFormFactory @inject */
    public $secureFormFactory;



    public function actionDefault()
    {
//        $this->ajaxRedirect("Form:");

        $section = $this->getSection('play');
        if (isset($section->uid)) {
//            $this->ajaxRedirect("Form:");
        }
    }



    /**
     * @return \FrontModule\Forms\SecureForm
     */
    protected function createComponentSecureForm($name)
    {
        $form = $this->secureFormFactory->create();
        $form
            ->setTranslator($this->translator->domain('site.' . $name))
            ->create();

        $form->bootstrap3Render();
        $form->onSuccess[] = function ($form, $values) {

            $section = $this->getSection('play');
            $section->setExpiration('+60 minutes');
            $section->uid = $values->code;
            $section->time = new DateTime('+60 minutes');

            $this->ajaxRedirect(":Front:Form:");
        };

        return $form;
    }


    /**
     * feature, not use yet
     *
     * @param $values
     * @throws \Exception
     */
    private function formSuccess($values)
    {
        /** @var UserEntity $entity */
        if ($entity = $this->userRepository->findOneBy(['username' => $values->code])) {

            /*
            if ($values->code != 123123) {
                $entity->setActive(false);
                $entity->setActiveDateTime(new DateTime());
                $this->userRepository->getEntityManager()->persist($entity)->flush();
            }
            */

            $section = $this->getSection('play');
            $section->setExpiration('+60 minutes');
            $section->uid = $values->code;
            $section->time = new DateTime('+60 minutes');
        }

    }

}
