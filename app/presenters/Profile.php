<?php

namespace App\Presenters;

use Nette;
use Nette\Application\Responses\JsonResponse;

class Profile extends Base {

    /** @var \App\Forms\SignFormFactory @inject */
    public $sign;

    /** @var \App\Forms\ProfileFormFactory @inject */
    public $form;

    /** @var \App\Models\Profile @inject */
    public $profile;

    public function startup() {
        parent::startup();

        if (!$this->user->isLoggedIn() AND $this->getAction() !== 'auth' AND $this->getAction() !== 'default') {
            $this->redirect('Profile:auth');
        }
        elseif ($this->user->isLoggedIn() AND $this->getAction() == 'auth') {
            $this->redirect('Profile:');
        }
    }

    public function renderDefault($id) {
        if ($id == null) {
            if (!$this->user->isLoggedIn()) {
                $this->redirect('Profile:auth');
            }
            $id = $this->user->getId();
        }

        $this->template->profile = $this->profile->getUser($id);
        $this->template->VI = $this->profile->getVisibleIcons($this->template->profile);
        $this->template->ats = $this->profile->allowShow($this->template->profile, $id);
        $this->template->editLink = $id == $this->user->getId() ? true : false;

        //TODO: Plnit opravdovými daty...
        $this->template->json = json_encode([
            ['value'=>1, 'label'=>'Veřejné'],
            ['value'=>1, 'label'=>'Pro uživatele'],
            //['value'=>0, 'label'=>'Skupinové'],
            ['value'=>1, 'label'=>'Soukormé'],
        ]);
    }

    public function renderAuth() {

    }

    public function renderEdit() {

    }

    public function actionLogout() {
        $this->user->logout(true);
        $this->flashMessage('Ohlášení bylo úspěšné.', 'alert-info');
        $this->redirect('Homepage:');
    }

    public function createComponentFormLogin() {
        $form = $this->sign->createIn();
        $form->onSuccess[] = function() {
            $this->getPresenter()->redirect('Profile:');
        };

        return $form;
    }

    public function createComponentFormRegistration() {
        $form = $this->sign->createRegistration();
        $form->onSuccess[] = function() {
            $this->getPresenter()->redirect('Profile:edit');
        };

        return $form;
    }

    public function createComponentFormEdit() {
        $form = $this->form->createEdit();
        $form->onSuccess[] = function() {
            $this->getPresenter()->flashMessage('Profil úsplěšně aktualizován', 'alert-success');
            $this->getPresenter()->redirect('Profile:');
        };

        return $form;
    }
}
