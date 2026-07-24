<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\ReportModel;

class ReportController extends BaseController
{
    public function index()
    {
        $reportModel = new ReportModel();

        // Ajout de item.lien, item.episode, item.saison dans le SELECT
        $reports = $reportModel
            ->select('reports.*, item.titre as item_titre, item.lien as item_lien, item.episode as item_episode, item.saison as item_saison, users.username')
            ->join('item', 'reports.item_id = item.id', 'left')
            ->join('users', 'reports.user_id = users.id', 'left')
            ->orderBy('reports.status', 'ASC')
            ->orderBy('reports.created_at', 'DESC')
            ->findAll()
        ;

        return view('admin/reports/index', ['reports' => $reports]);
    }

    public function resolve($id)
    {
        $reportModel = new ReportModel();
        $report = $reportModel->find($id);

        if ($report) {
            $reportModel->update($id, ['status' => 'resolved']);

            $audit = new AuditLogModel();
            $audit->logAction('Résolution Signalement', "Le signalement ID {$id} pour la carte ID {$report['item_id']} a été marqué comme résolu.");

            return redirect()->back()->with('message', 'Le signalement a été marqué comme résolu.');
        }

        return redirect()->back()->with('error', 'Signalement introuvable.');
    }

    public function delete($id)
    {
        $reportModel = new ReportModel();
        if ($reportModel->find($id)) {
            $reportModel->delete($id);

            return redirect()->back()->with('message', 'Le signalement a été supprimé.');
        }

        return redirect()->back()->with('error', 'Signalement introuvable.');
    }
}
