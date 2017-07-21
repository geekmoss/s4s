<?php

namespace App\Presenters;

use App\Models\Authorizator;
use Nette;

/**
 * Base presenter
 */
abstract class Base extends Nette\Application\UI\Presenter
{
    /**
     * @var \Nette\Security\User @inject
     */
    public $user;

    public function startup() {
        parent::startup();

        $this->user->setAuthorizator(new Authorizator());
        $this->user->authorizator->setup();

        $this->template->loggedIn = $this->user->loggedIn;

        $this->template->nav = $this->getNav();
        $this->template->url = $this->getHttpRequest()->getUrl()->getHostUrl();
    }

    /**
     * Metoda vracející pole pro generování navigace
     * @return array
     */
    private function getNav() {
        $nav = [];

        // Profile - Rozdílné položky při přihlášeném stavu
        if ($this->user->isLoggedIn()) {
            $nav[] = (object)array(
                'label' => 'Materiály',
                'presenter' => 'Materials:',
                'items' => array(
                    (object)array(
                        'presenter' => 'Materials:',
                        'action' => '',
                        'label' => 'Seznam',
                        'type' => 'link',
                    ),
                    (object)array(
                        'presenter' => 'Materials:',
                        'action' => 'management',
                        'label' => 'Správa materiálů',
                        'type' => 'link',
                    ),
                    (object)array(
                        'presenter' => 'Materials:',
                        'action' => 'upload',
                        'label' => 'Nahrát nové',
                        'type' => 'link',
                    ),
                ),
                'type' => 'dropdown'
            );
            $nav[] = (object)array(
                'label' => 'Profil',
                'presenter' => 'Profile:',
                'items' => array(
                    (object)array(
                        'presenter' => 'Profile:',
                        'action' => '',
                        'label' => 'Přehled',
                        'type' => 'link',
                    ),
                    (object)array(
                        'presenter' => 'Profile:',
                        'action' => 'logout',
                        'label' => 'Odhlášení',
                        'type' => 'link',
                    ),
                ),
                'type' => 'dropdown'
            );
        }
        // Při nepříhlášeném stavu
        else {
            $nav[] = (object)array(
                'presenter' => 'Materials:',
                'action' => '',
                'label' => 'Materiály',
                'type' => 'link',
            );
            $nav[] = (object)array(
                'presenter' => 'Profile:',
                'action' => 'auth',
                'label' => 'Přihlášení / Registrace',
                'type' => 'link',
            );
        }

        return $nav;
    }

}
