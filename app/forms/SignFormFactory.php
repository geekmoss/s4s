<?php

namespace App\Forms;

use App\Models\Authorizator;
use Nette,
    Nette\Application\UI\Form,
    Nette\Security\User,
    Instante;

/**
 * Přihlašovací a registrační formuláře
 */
class SignFormFactory extends Nette\Object {
    /** @var User */
    private $user;

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(User $user, Nette\Database\Context $database) {
        $this->user = $user;
        $this->database = $database;
    }

    /**
     * Metoda vracející Form pro přihlášení
     * @return Form
     */
    public function createIn() {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Vyplňte prosím uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Vyplňte prosím přihlašovací heslo.');

        $form->addCheckbox('remember', 'Pamatovat si mne');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = array($this, 'formSucceededIn');

        $form->setRenderer(new Instante\Bootstrap3Renderer\BootstrapRenderer);

        return $form;
    }

    /**
     * Metoda vracející Form pro odhlášení
     * @return Form
     */
    public function createOut() {
        $form = new Form;
        $form->addSubmit('send', 'Odhlásit');

        $form->onSuccess[] = array($this, 'formSucceededOut');

        $form->setRenderer(new Instante\Bootstrap3Renderer\BootstrapRenderer);

        return $form;
    }

    /**
     * Metoda vracející Form pro registraci
     * @return Form
     */
    public function createRegistration() {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')->setRequired('Vyplňte prosím uživatelské jméno.');
        $form->addText('email', 'E-mail')->addRule(Form::EMAIL, 'Zadejte platný email.')->setRequired('Vyplňte prosím E-Mailovou adresu.');
        $form->addPassword('pass', 'Heslo')->setRequired('Vyplňte prosím přihlašovací heslo.');
        $form->addPassword('pass2', 'Heslo znovu')->setRequired('Zopakujte prosím přihlašovací heslo.');
        $form->addSubmit('send','Registrovat');

        $form->addProtection('Vypršel časový limit, odešlete formulář znovu.');

        $form->onValidate[] = array($this, 'validateRegistrationForm');
        $form->onSuccess[] = array($this, 'formSucceededRegistration');

        $form->setRenderer(new Instante\Bootstrap3Renderer\BootstrapRenderer);

        return $form;
    }

    /**
     * @param Form $form
     * @param $values
     */
    public function formSucceededIn($form, $values) {
        if ($values->remember) {
            $this->user->setExpiration('14 days', FALSE);
        }
        else {
            $this->user->setExpiration('60 minutes', TRUE);
        }

        try {
            $this->user->login($values->username, $values->password);
        }
        catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    /**
     * @param $form
     * @param $values
     */
    public function formSucceededOut($form, $values) {
        $this->user->logout(true);
    }

    /**
     * @param Form $form
     */
    public function validateRegistrationForm($form) {
        $values = $form->getValues();
        if ($values->pass !== $values->pass2) {
            $form->addError('Hesla se neshodují.');
        }

        if ($this->database->query('SELECT * '.' FROM users WHERE email=?',$values->email)->fetchAll() == 0) {
            $form->addError('Zadaný E-Mail je již zaregistrován.');
        }
        if ($this->database->query('SELECT * '.' FROM users WHERE username=?',$values->username)->fetchAll() == 0) {
            $form->addError('Zadané uživatelské jméno je již zabrané.');
        }
    }

    /**
     * @param Form $form
     * @param $values
     */
    public function formSucceededRegistration($form, $values) {
        $res = $this->database->table('users')->insert(array(
            'username' => $values->username,
            'email' => $values->email,
            'password' => \Nette\Security\Passwords::hash($values->pass)
        ));

        if ($res) {
            $form->getPresenter()->flashMessage('Registrace proběhla úspěšně.', 'alert-success');
        }
        else {
            $form->getPresenter()->flashMessage('Registrace se nepovedla, zkuste to znovu.', 'alert-danger');
        }

        try {
            $this->user->login($values->username, $values->pass);
            $form->getPresenter()->flashMessage('Dovolili jsme si Vás automaticky přihlásit. Vítejte u nás!', 'alert-info');
        }
        catch (Nette\Security\AuthenticationException $e) {
            $form->getPresenter()->flashMessage('Automatické přihlášení se nezdařilo, přihlašte se prosím.', 'alert-info');
        }
    }

}