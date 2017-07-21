<?php

namespace App\Models;

use Nette;

class Profile extends Nette\Object {

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

    /**
     * @param $id
     * @return mixed
     */
    public function getUser($id) {
        return $this->database->table('users')->where('id = ?', $id)->fetch();
    }

    public function getVisibleIcons($profile) {
        return $this->visibility->getVisibleIconsProfile($profile);
    }

    public function allowShow($profile, $id) {
        return $this->visibility->allowShowProfile($profile, $id);
    }
}