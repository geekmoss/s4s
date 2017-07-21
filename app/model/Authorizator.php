<?php

namespace App\Models;

use Nette;

class Authorizator extends Nette\Security\Permission {

    const ROLE_GUEST = "guest";
    const ROLE_USER = "user";
    const ROLE_TRUSTWORTHY = "trustworthy";
    const ROLE_ADMIN = "admin";

    const RES_VERIFIED = "verified";
    const RES_ADMIN = "admin";

    const OP_VIEW = "view";
    const OP_EDIT = "edit";

    /**
     * Nastavení rolí a pravidel
     */
    public function setup() {
        $this->addRole(self::ROLE_GUEST);
        $this->addRole(self::ROLE_USER);
        $this->addRole(self::ROLE_TRUSTWORTHY);
        $this->addRole(self::ROLE_ADMIN);

        $this->addResource(self::RES_VERIFIED);
        $this->addResource(self::RES_ADMIN);

        $this->allow(self::ROLE_TRUSTWORTHY, self::RES_VERIFIED, self::OP_EDIT);
        $this->allow(self::ROLE_ADMIN, self::ALL, self::ALL);

        $this->roleInheritsFrom(self::ROLE_USER, self::ROLE_GUEST);
        $this->roleInheritsFrom(self::ROLE_TRUSTWORTHY, self::ROLE_USER);
    }

}