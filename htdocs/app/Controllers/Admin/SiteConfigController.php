<?php declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiteConfigModel;
use App\Models\AuditLogModel;

class SiteConfigController extends BaseController
{
    private $model;

    public function __construct()
    {
        $this->model = new SiteConfigModel();
    }

    /**
     * Sécurité supplémentaire pour s'assurer que seul le superadmin y accède
     */
    private function checkSuperAdmin()
    {
        if (!auth()->loggedIn() || !auth()->user()->inGroup('superadmin')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    public function index()
    {
        $this->checkSuperAdmin();
        $data = [
            'configs' => $this->model->findAll()
        ];
        return view('admin/site_config/index', $data);
    }

    public function form($id = null)
    {
        $this->checkSuperAdmin();

        $config = null;
        if ($id !== null) {
            $config = $this->model->find($id);
            if (!$config) {
                return redirect()->to('a/sc')->with('error', 'Configuration introuvable.');
            }
        }

        return view('admin/site_config/form', ['config' => $config]);
    }

    public function save()
    {
        $this->checkSuperAdmin();

        $rules = [
            'domain' => 'required',
            'regex_episode' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $indPage = $this->request->getPost('indicateurs_page_invalide') ?: '[]';
        $indLec = $this->request->getPost('indicateurs_lecteur') ?: '[]';

        if (json_decode($indPage) === null || json_decode($indLec) === null) {
            return redirect()->back()->withInput()->with('error', 'Le format JSON des indicateurs est invalide.');
        }

        $id = $this->request->getPost('id');
        $data = [
            'domain' => $this->request->getPost('domain'),
            'regex_episode' => $this->request->getPost('regex_episode'),
            'indicateurs_page_invalide' => $indPage,
            'indicateurs_lecteur' => $indLec,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($id) {
            $this->model->update($id, $data);
            (new AuditLogModel())->logAction('Mise à jour Config', "Le SuperAdmin a modifié la configuration du domaine '{$data['domain']}'.");
            $message = 'Configuration mise à jour avec succès.';
        } else {
            $this->model->insert($data);
            (new AuditLogModel())->logAction('Création Config', "Le SuperAdmin a ajouté la configuration pour le domaine '{$data['domain']}'.");
            $message = 'Nouvelle configuration ajoutée avec succès.';
        }

        // Vide le cache pour appliquer la nouvelle config immédiatement sur checkDispo()
        cache()->delete('parsing_sites_config');

        return redirect()->to('a/sc')->with('message', $message);
    }

    public function delete($id)
    {
        $this->checkSuperAdmin();
        $config = $this->model->find($id);

        if ($config) {
            $this->model->delete($id);
            cache()->delete('parsing_sites_config');
            (new AuditLogModel())->logAction('Suppression Config', "Le SuperAdmin a supprimé la configuration du domaine '{$config['domain']}'.");
            return redirect()->back()->with('message', 'Configuration supprimée.');
        }

        return redirect()->back()->with('error', 'Configuration introuvable.');
    }
}
