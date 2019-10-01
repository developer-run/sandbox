<?php

namespace FrontModule\Forms;

use Devrun\Doctrine\Entities\UserEntity;
use Devrun\Doctrine\Repositories\UserRepository;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Tracy\Debugger;

interface ISecureFormFactory
{
    /** @return SecureForm */
    function create();
}

/**
 * Class SecureForm
 * @package FrontModule\Forms
 */
class SecureForm extends BaseForm
{

    /** @var UserRepository @inject */
    public $userRepository;


    /**
     * @return SecureForm
     */
    public function create()
    {
        $this->addText('code', 'code')
            ->addRule(Form::FILLED, 'code.required')
            ->setAttribute('class', 'input-md text-center')
            ->setAttribute('placeholder', 'code.placeholder');

        $this->addSubmit('send', 'send')
            ->setAttribute('class', 'arrow');

        $this->onValidate[] = [$this, 'validateCode'];
//        $this->onSuccess[] = [$this, 'success'];

        return $this;
    }


    public function validateCode(SecureForm $form, $values)
    {
        /** @var UserEntity $entity */
        if (!$entity = $this->userRepository->findOneBy(['username' => $values->code])) {
            $form->addError('code.not_found');
            return false;
        }

        if (!$entity->isActive()) {
            $form->addError('code.already_used');
            return false;
        }

        return true;
    }




}