<?php

namespace App\Models;

use Nette;

class Visibility extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;

    /** @var Nette\Security\User */
    private $user;

    /**
     * @param Nette\Database\Context $database
     * @param Nette\Security\User    $user
     */
    public function __construct(Nette\Database\Context $database, Nette\Security\User $user) {
        $this->database = $database;
        $this->user = $user;
    }

    public function getVisibleIconsProfile($profile) {
        $icons = new \stdClass();
        $visibility = $this->database->table('visibility_levels')->fetchAll();
        $v = [
            'email' => $profile->visible_email,
            'fullname' => $profile->visible_fullname,
            'web' => $profile->visible_web,
            'bio' => $profile->visible_bio];

        foreach ($v as $key => $val) {
            $icons->$key = $visibility[$val]->{'css-icon'};
        }

        return $icons;
    }

    public function getVisibleIconsMaterials($materials) {
        $v = [];
        $visibility = $this->database->table('visibility_levels')->fetchAll();
        foreach ($materials as $m) {
            $v[$m->id] = $visibility[$m->vid]->{'css-icon'};
        }
        return $v;
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
                case 1:
                    $bools->{$key} = $id == $this->user->getId() ? true : false; // Soukromé
                    break;
                case 2:
                    //TODO: Dokončit ověřování skupin
                    $bools->{$key} = $this->user->getId() ? true : false; // Skupina
                    break;
                case 3:
                    $bools->{$key} = $this->user->isLoggedIn(); // Pro všechny registrované uživatele
                    break;
                case 4:
                    $bools->{$key} = true; // Veřejné
                    break;
            }
        }
        return $bools;
    }

}