<?php

namespace App\Presenters;

use App\Models\Authorizator;
use Nette;

class Materials extends Base {

    /** @var \App\Forms\MaterialsFormFactory @inject */
    public $form;

    /** @var \App\Models\Materials @inject */
    public $materials;

    /** @var \App\Models\Karma @inject */
    public $karma;

    /**
     * @param string $id
     */
    public function renderDefault($id) {
        $materials = $this->materials->getMaterialsList($id, $this->getHttpRequest()->getQuery('search'), $this->getHttpRequest()->getQuery('order'));
        $this->template->category = $this->materials->getCategories();
        $this->template->active_category = $id;
        $this->template->materials = $materials;
        $this->template->search_val = $this->getHttpRequest()->getQuery('search') == null ? '' : $this->getHttpRequest()->getQuery('search');
    }

    /**
     *
     */
    public function renderUpload() {

    }

    /**
     *
     */
    public function renderManagement() {
        $this->template->materials = $this->materials->getMaterials($this->user->getId());
        $this->template->vis_icons = $this->materials->getVisibilityIcons($this->template->materials);
    }

    /**
     * @param string $id
     */
    public function renderEdit($id) {
        if ($id === null) {
            $this->flashMessage('Vyberte položku, kterou chcete upravovat.', 'alert-warning');
            $this->redirect('Materials:management');
        }
        $material = $this->materials->getMaterial($id);

        if ($material === false) {
            $this->flashMessage('Práce nebyla nalezena.', 'alert-warning');
            $this->redirect('Materials:management');
        }

        if ($material->author !== $this->user->getId() AND !$this->user->isInRole(Authorizator::ROLE_ADMIN)) {
            $this->flashMessage('Pro manipulaci s prací nemáte dostatečná oprávnění.', 'alert-danger');
            $this->redirect('Materials:mamagenet');
        }

        $this->template->material = $material;
        $this->getSession('editMaterial')->id = $id;
    }

    /**
     * @param string $id
     */
    public function renderOverview($id) {
        if ($id === null) {
            $this->redirect('Materials:default');
        }

        $material = $this->materials->getMaterialFromView($id);

        if ($material === false) {
            $this->flashMessage('Práce nebyla nalezena.', 'alert-warning');
            $this->redirect('Materials:default');
        }

        $this->template->material = $material;
        $this->template->isLoggedIn = $this->user->isLoggedIn();
        if ($this->user->isLoggedIn()) {
            $this->template->voted = $this->karma->alreadyVoted($this->user->id, $material->id);
        }
    }

    /**
     * @param string $id
     */
    public function actionDownload($id) {
        if ($id === null) {
            $this->flashMessage('Požadovaná práce nebyla nalezena.', 'alert-warning');
            $this->redirect('Materials:default');
        }

        $material = $this->materials->getMaterial($id);

        if ($material === false) {
            $this->flashMessage('Požadovaná práce nebyla nalezena.', 'alert-warning');
            $this->redirect('Materials:default');
        }

        $this->flashMessage('Stahování bylo zahájeno.', 'alert-info');

        $this->sendResponse(new Nette\Application\Responses\FileResponse(__ROOT__.'/files/uploaded/'.$material->hash.$material->extension));

        $this->redirect('Materials:overview');
    }

    /**
     * @param string $id
     */
    public function actionRemove($id) {
        if ($id === null) {
            $this->flashMessage('Požadovaná práce nebyla nalezena.', 'alert-warning');
            $this->redirect('Materials:management');
        }

        $material = $this->materials->getMaterial($id);

        if ($material === false) {
            $this->flashMessage('Požadovaná práce nebyla nalezena.', 'alert-warning');
            $this->redirect('Materials:management');
        }
        if ($material->author !== $this->user->getId()) {
            $this->flashMessage('Tato práce Vám nepatří!', 'alert-danger');
            $this->redirect('Materials:default');
        }

        $this->materials->removeMaterial($id);
        $this->flashMessage('Práce byla úspěšně odstraněna ze serveru.', 'alert-success');

        $this->redirect('Materials:management');
    }

    public function actionOverview($id) {
        if ($this->user->isLoggedIn() AND $this->getHttpRequest()->getQuery('vote') !== null AND $this->isAjax()) {
            $vote = $this->getHttpRequest()->getQuery('vote') == 'up' ? true : false;
            $this->karma->vote($vote, $id);
        }
    }

    public function createComponentFormUpload() {
        $form = $this->form->createUpload();

        return $form;
    }

    public function createComponentFormEdit() {
        $form = $this->form->createEdit($this->getSession('editMaterial')->id);

        return $form;
    }
}
