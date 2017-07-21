<?php

namespace App\Forms;

use Nette,
    Nette\Application\UI\Form,
    Instante;

class ProfileFormFactory extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;

    /** @var Nette\Security\User */
    private $user;

    public function __construct(Nette\Database\Context $database, Nette\Security\User $user) {
        $this->database = $database;
        $this->user = $user;
    }

    public function createEdit() {
        $form = new Form();
        $profile = $this->database->table('users')->where('id = ?', $this->user->getId())->fetch();
        $visibility_level = $this->database->table('visibility_levels')->fetchAll();
        $vl = [];
        foreach ($visibility_level as $key => $v_l) {
            $vl[$v_l->id] = $v_l->title;
        }

        $form->addGroup('E-Mail:');
        $form->addText('email', 'E-Mail:')
            ->setDefaultValue($profile->email);
        $form->addSelect('visible_email', 'Viditelnost', $vl)
            ->setDefaultValue($profile->visible_email);

        $form->addGroup('Celé jméno');
        $form->addText('fullname', 'Jméno:')
            ->setDefaultValue($profile->fullname);
        $form->addSelect('visible_fullname', 'Viditelnost', $vl)
            ->setDefaultValue($profile->visible_fullname);

        $form->addGroup('Webové stránky');
        $form->addText('web', 'URL:')
            ->setDefaultValue($profile->web);
        $form->addSelect('visible_web', 'Viditelnost:', $vl)
            ->setDefaultValue($profile->visible_web);

        $form->addGroup('Něco o mne:');
        $form->addTextArea('bio', 'Text:')
            ->setDefaultValue($profile->bio);
        $form->addSelect('visible_bio', 'Viditelnost:', $vl)
            ->setDefaultValue($profile->visible_bio);

        $form->addSubmit('send', 'Aktualizotvat');

        $form->onSuccess[] = array($this, 'formSucceededEdit');

        $form->setRenderer(new Instante\Bootstrap3Renderer\BootstrapRenderer());

        return $form;
    }

    /**
     * @param Form $form
     * @param      $values
     */
    public function formSucceededEdit(Form $form, $values) {
        $res = $this->database->table('users')->where('id = ?', $this->user->getId())->update($values);
    }
}