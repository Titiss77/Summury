<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\ReportModel; // Ajout du modèle des signalements

class AuditController extends BaseController
{
    public function index()
    {
        $auditModel = new AuditLogModel();
        $reportModel = new ReportModel();

        $data = [
            // Récupère les 200 dernières actions pour éviter de surcharger la page
            'logs' => $auditModel->getRecentLogs(200),
            // Compte des signalements avec le statut "pending" (en attente)
            'pendingReportsCount' => $reportModel->where('status', 'pending')->countAllResults(),
        ];

        return view('admin/audit/index', $data);
    }
}
