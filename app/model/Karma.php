<?php

namespace App\Models;

use Nette;

class Karma extends Nette\Object {

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

    /**
     * Metoda snižuje / zvyžuje karmu materiálu a profilu
     * @param bool   $positive
     * @param string $hash
     */
    public function vote($positive, $hash) {
        if ($this->user->isLoggedIn()) {
            $karma_voted = $this->database->table('karma_vote');
            // Karma - file
            $row = $this->database->table('materials')->where('hash = ?', $hash);
            $fetch_row = $row->fetch();
            $karma = $fetch_row->karma;
            $user  = $fetch_row->author;
            $id    = $fetch_row->id;

            // Ohodnotil uživatel Y materiál X - ochrana před stakcováním hodnocení
            if ($karma_voted->where('user = ? AND material = ?', $user, $id)->fetch() === false AND $user != $this->user->getId()) {
                if ($positive) {
                    $karma++;
                }
                else {
                    $karma--;
                }
                $row->update(['karma' => $karma]);
                // Karma - user
                $row_user = $this->database->table('users')->where('id = ?', $user);
                $files_karma = $this->database->table('materials')->where('author = ?',$user)->sum('karma');
                $user_karma = $row_user->fetch()->karma;
                $round_user_karma = round($files_karma / 10, 0, PHP_ROUND_HALF_DOWN);
                if ($round_user_karma != $user_karma) {
                    $row_user->update(['karma' => $round_user_karma]);
                }
                // Ohodnotil uživatel Y materiál X - ochrana před stakcováním hodnocení
                $karma_voted->insert(['user' => $user, 'material' => $id]);
            }
        }
    }

    /**
     * Metoda vrací zaznamené uživatele ke konrétním ohodnoceným materiálům
     * @param int $user
     * @param int $material
     * @return array|Nette\Database\Table\IRow[]
     */
    public function alreadyVoted($user, $material) {
        return $this->database->table('karma_vote')->where('user = ? AND material = ?', $user, $material)->fetch();
    }
}