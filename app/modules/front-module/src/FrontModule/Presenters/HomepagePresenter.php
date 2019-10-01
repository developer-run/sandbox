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

    /** @var UserRepository @inject */
    public $userRepository;


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

            $this->ajaxRedirect("Form:");
        };

        return $form;
    }

}
