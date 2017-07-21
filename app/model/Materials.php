<?php

namespace App\Models;

use Nette;

class Materials extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;

    /** @var Nette\Security\User */
    private $user;

    /** @var \App\Models\Visibility */
    private $visibility;

    /**
     * @param Nette\Database\Context $database
     * @param Nette\Security\User    $user
     */
    public function __construct(Nette\Database\Context $database, Nette\Security\User $user) {
        $this->database = $database;
        $this->user = $user;
        $this->visibility = new Visibility($database, $user);
    }

    public function getMaterials($user) {
        return $this->database->table('v_materials')->setPrimarySequence('id')->where('aid = ?', $user)->fetchAll();
    }

    public function getMaterialsList($category = null, $pattern = null, $order = null) {
        $q = $this->database->table('v_materials')->setPrimarySequence('id');

        if ($category !== null) {
            $q->where('category = ?', $category);
        }

        if ($pattern !== null) {
            $where = '';
            $arg = [];
            $cols = $this->database->query("SHOW COLUMNS FROM v_materials")->fetchAll();
            $execude = ['aid', 'vid', 'lid', 'cid', 'for_group', 'timestamp', 'ts', 'visibility'];
            foreach ($cols as $col) {
                if (!in_array($col->Field, $execude)) {
                    $where.= $col->Field.' LIKE ? OR ';
                    $arg[] = "%$pattern%";
                }
            }
            $where = substr($where, 0, -4);

            $q->where($where, $arg);
        }
        // Registred
        if ($this->user->isLoggedIn()) {
            $q->where('vid = 4 OR vid = 3');
        }
        else {
            $q->where('vid = 4');
        }

        if ($order !== null) {
            switch ($order) {
                case 'tsasc':
                    $q->order('timestamp ASC');
                    break;
                case 'karma':
                    $q->order('karma DESC');
                    break;
                default:
                    $q->order('timestamp DESC');
                    break;
            }
        }
        else {
            $q->order('timestamp DESC');
        }

        return $q->fetchAll();
    }

    public function getMaterial($id) {
        return $this->database->table('materials')->where('hash = ?', $id)->fetch();
    }
    public function getMaterialFromView($id) {
        return $this->database->table('v_materials')->setPrimarySequence('id')->where('hash = ?', $id)->fetch();
    }
    public function getCategories() {
        return $this->database->table('category')->fetchAll();
    }


    public function removeMaterial($hash) {
        return $this->database->table('materials')->where('hash = ? AND author = ?', $hash, $this->user->getId())->delete();
    }

    /**
     * Metoda vrací TRUE/FALSE k jednotlivým prvkům
     * @param $profile
     * @param $id
     * @return \stdClass
     */
    public function allowShowProfile($profile, $id) {
        $bools = new \stdClass();

        $v = [
            'email' => $profile->visible_email,
            'fullname' => $profile->visible_fullname,
            'web' => $profile->visible_web,
            'bio' => $profile->visible_bio
        ];

        foreach ($v as $key => $val) {
            switch ($val) {
                case 0:
                    $bools->{$key} = $id == $this->user->getId() ? true : false; // Soukromé
                    break;
                case 1:
                    //TODO: Dokončit ověřování skupin
                    $bools->{$key} = $this->user->getId() ? true : false; // Skupina
                    break;
                case 2:
                    $bools->{$key} = $this->user->isLoggedIn(); // Pro všechny registrované uživatele
                    break;
                case 3:
                    $bools->{$key} = true; // Veřejné
                    break;
            }
        }
        return $bools;
    }

    public function getVisibilityIcons($materials) {
        return $this->visibility->getVisibleIconsMaterials($materials);
    }

}