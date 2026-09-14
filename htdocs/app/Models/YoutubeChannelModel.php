<?php declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class YoutubeChannelModel extends Model
{
    protected $table = 'youtube_channels';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['user_id', 'channel_id', 'channel_name', 'last_video_id', 'created_at'];
    protected $useTimestamps = true;
    protected $updatedField = ''; // Désactivé car non nécessaire ici
}