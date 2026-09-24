<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\ReportModel;

class AuditController extends BaseController
{
    /**
     * Affiche le journal complet de sécurité et le récapitulatif des signalements.
     */
    public function index()
    {
        $auditModel = new AuditLogModel();
        $reportModel = new ReportModel();

        $data = [
            'logs' => $auditModel->getRecentLogs(200),
            'pendingReportsCount' => $reportModel->where('status', 'pending')->countAllResults(),
        ];

        return view('admin/audit/index', $data);
    }
}
