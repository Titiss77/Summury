<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\ReportModel;

class ReportController extends BaseController
{
    public function submit()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Accès refusé.');
        }

        $json = $this->request->getJSON();

        if (!isset($json->item_id) || !isset($json->type)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Données incomplètes.']);
        }

        $reportModel = new ReportModel();
        $reportModel->insert([
            'item_id' => $json->item_id,
            'user_id' => auth()->loggedIn() ? auth()->id() : null,
            'type' => $json->type,
            'description' => $json->description ?? '',
            'status' => 'pending',
        ]);

        // Optionnel : Loguer l'action
        $audit = new AuditLogModel();
        $audit->logAction('Nouveau Signalement', "L'utilisateur a signalé un problème (Type: {$json->type}) sur la carte ID {$json->item_id}.");

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Signalement envoyé avec succès.',
            'csrf_token' => csrf_hash(), // Renouvellement du token CSRF
        ]);
    }
}
