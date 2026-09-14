<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\YoutubeChannelModel;

class YoutubeController extends BaseController
{
    public function add()
    {
        $model = new YoutubeChannelModel();
        // On récupère uniquement les chaînes surveillées par l'utilisateur connecté
        $channels = $model->where('user_id', auth()->id())->findAll();
        
        return view('youtube/add', ['channels' => $channels]);
    }

    public function save()
    {
        $channelId = $this->request->getPost('channel_id');
        $channelName = $this->request->getPost('channel_name');

        if (!empty($channelId) && !empty($channelName)) {
            $model = new YoutubeChannelModel();
            
            // Éviter les doublons pour un même utilisateur
            $exists = $model->where('user_id', auth()->id())
                            ->where('channel_id', $channelId)
                            ->first();

            if (!$exists) {
                $model->insert([
                    'user_id' => auth()->id(),
                    'channel_id' => trim($channelId),
                    'channel_name' => trim($channelName),
                    'last_video_id' => null
                ]);
                return redirect()->back()->with('message', 'La chaîne a été ajoutée à la surveillance automatique.');
            }
            return redirect()->back()->with('error', 'Cette chaîne est déjà surveillée.');
        }
        return redirect()->back()->with('error', 'Veuillez remplir tous les champs.');
    }

    public function delete($id)
    {
        $model = new YoutubeChannelModel();
        $channel = $model->find($id);

        // Vérification de sécurité : on s'assure que la chaîne appartient bien à l'utilisateur
        if ($channel && (int) $channel['user_id'] === (int) auth()->id()) {
            $model->delete($id);
            return redirect()->back()->with('message', 'La chaîne a été supprimée de la surveillance.');
        }

        return redirect()->back()->with('error', 'Impossible de supprimer cette chaîne.');
    }
}