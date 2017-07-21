<?php

namespace App\Forms;

use Nette,
    Nette\Application\UI\Form,
    Instante;

class MaterialsFormFactory extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;

    /** @var Nette\Security\User */
    private $user;

    public function __construct(Nette\Database\Context $database, Nette\Security\User $user) {
        $this->database = $database;
        $this->user = $user;
    }

    public function createUpload() {
        $form = new Form();

        $form->addUpload('file', 'Vyberte soubor:')
            ->addRule($form::MAX_FILE_SIZE, 'Maximální velikost nahrávaného souboru je 16 MB.', 16 * 1024 * 1024)
            ->setAttribute('class', 'file')
            ->setHtmlId('fileupload');

        $form->onSuccess[] = array($this, 'formSucceededUpload');

        $form->setRenderer(new Instante\Bootstrap3Renderer\BootstrapRenderer());

        return $form;
    }

    /**
     * @param $hash
     * @return Form $form
     */
    public function createEdit($hash) {
        $data = $this->database->table('materials')->where('hash = ? AND author = ?',$hash, $this->user->getId())->fetch();
        $visibility_level = $this->database->table('visibility_levels')->fetchAll();
        $category = $this->database->table('category')->fetchAll();
        $license = $this->database->table('licenses')->fetchAll();
        $vl = [];
        foreach ($visibility_level as $key => $v_l) {
            $vl[$v_l->id] = $v_l->title;
        }
        $cat = [];
        foreach ($category as $c) {
            $cat[$c->id] = $c->name . " ($c->abbreviation)";
        }
        $lic = [];
        foreach ($license as $l) {
            $lic[$l->id] = $l->title;
        }
        $form = new Form();

        $form->addHidden('hash', $hash);

        $form->addText('name', 'Název práce:')
            ->setDefaultValue($data->name)
            ->setRequired();

        $form->addSelect('visible', 'Viditelnost:', $vl)
            ->setDefaultValue($data->visible);

        $form->addSelect('license', 'Licence', $lic)
            ->setDefaultValue($data->license);

        $form->addSelect('category', 'Kategorie:', $cat);
        if ($data->category !== null) {
            $form['category']->setDefaultValue($data->category);
        }

        $form->addTextArea('description', 'Popis práce:')
            ->setDefaultValue($data->description);


        $form->addSubmit('send', 'Uložit');

        $form->setRenderer(new Instante\Bootstrap3Renderer\BootstrapRenderer());
        $form->onSuccess[] = array($this, 'formSucceededEdit');

        return $form;
    }

    /**
     * @param Form $form
     * @param      $values
     */
    public function formSucceededUpload(Form $form, $values) {
        /** @var \Nette\Http\FileUpload $file */
        $file = $values->file;
        $path = __ROOT__.'/files/uploaded/';

        if ($file->isOk()) {
            // oddělení přípony pro účel změnit název souboru na co chceš se zachováním přípony
            $file_ext=strtolower(mb_substr($file->getSanitizedName(), strrpos($file->getSanitizedName(), ".")));

            $hash = md5(sha1(time().date(DATE_RFC2822).rand(0, 9999).$this->user->getId()));
            // přesunutí souboru z temp složky
            $file->move($path.$hash.$file_ext);

            $data = [
                'name' => 'Dočasné jméno - '.$file->getSanitizedName(),
                'hash' => $hash,
                'extension' => $file_ext,
                'author' => $this->user->getId(),
                'license' => 1,
            ];
            $this->database->table('materials')->insert($data);

            $form->getPresenter()->flashMessage('Vaše práce byla úspěšně uložena na server, prosím dokončete publikaci nastavením dané práce.', 'alert-success');
            $form->getPresenter()->redirect('Materials:edit', ['id' => $hash]);
        }
        else {
            $form->getPresenter()->flashMessage('Přenos byl přerušen nebo data se při přenosu poškodila, zkuste prosím znovu nahrát Vaši práci.', 'alert-warning');
            $form->getPresenter()->redirect('Materials:upload');
        }
    }

    /**
     * @param Form $form
     * @param      $values
     */
    public function formSucceededEdit(Form $form, $values) {
        $this->database->table('materials')->where('hash = ? AND author = ?', $values->hash, $this->user->getId())->update($values);
        $form->getPresenter()->flashMessage('Práce úspěšně upravena.', 'alert-success');
        $form->getPresenter()->redirect('Materials:management');
    }
}