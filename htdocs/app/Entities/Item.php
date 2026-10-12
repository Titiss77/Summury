<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int|string $id
 * @property int|string $id_user
 * @property string     $titre
 * @property string     $episode
 * @property string     $total_episodes
 * @property string     $saison
 * @property string     $total_saisons
 * @property int        $episode_global
 * @property int        $total_episodes_global
 */
class Item extends Entity
{
    // Cette fonction remplace le gros bloc str_replace que tu avais dans home.php
    public function getFinalLink(): string
    {
        $lien = $this->attributes['lien'] ?? '#';
        $ep = $this->attributes['episode'] ?? '1';
        $saison = $this->attributes['saison'] ?? '1';

        $ep2 = str_pad((string) $ep, 2, '0', STR_PAD_LEFT);
        $ep3 = str_pad((string) $ep, 3, '0', STR_PAD_LEFT);
        $ep4 = str_pad((string) $ep, 4, '0', STR_PAD_LEFT);

        return str_replace(
            ['{ep}', '{s}', '{ep2}', '{ep3}', '{ep4}'],
            [$ep, $saison, $ep2, $ep3, $ep4],
            $lien
        );
    }
}
