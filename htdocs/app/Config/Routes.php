<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

// @var RouteCollection $routes

// --------------------------------------------------------------------
// Pages publiques (accessibles sans être connecté)
// --------------------------------------------------------------------
$routes->get('/', 'HomeController::index');
$routes->get('categorie/(:num)', 'HomeController::categorie/$1');

// Route silencieuse pour la tâche de fond (Pseudo-Cron)
$routes->get('cron/run', 'CronController::run');

// --------------------------------------------------------------------
// Routes protégées par session (Utilisateurs connectés normaux)
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'session'], static function ($routes): void {
    $routes->get('item/form', 'ItemController::form');
    $routes->get('item/form/(:num)', 'ItemController::form/$1');
    $routes->post('item/save', 'ItemController::save');

    // Note : Transformer ce GET en POST ou DELETE renforcera la protection CSRF
    $routes->get('item/delete/(:num)', 'ItemController::delete/$1');

    $routes->post('item/increment-episode/(:num)', 'ItemController::incrementEpisode/$1');
    $routes->post('items/update-order', 'ItemController::updateOrder');
    $routes->get('items/check-to-global', 'ItemController::checkToGlobal');
    $routes->get('item/turn/(:num)', 'ItemController::turnToAdmin/$1');
    $routes->get('item/search', 'ItemController::search');

    $routes->post('report/submit', 'ReportController::submit');
});

// --------------------------------------------------------------------
// Routes d'Administration (Restreintes par Session ET Rôle Shield)
// --------------------------------------------------------------------
// On applique le filtre Shield 'group' pour s'assurer que seuls les admins y accèdent.
$routes->group('users', ['namespace' => 'App\Controllers\Admin', 'filter' => 'group:superadmin,admin'], static function ($routes): void {
    $routes->get('/', 'UserController::index');
    $routes->get('edit/(:num)', 'UserController::edit/$1');
    $routes->post('update/(:num)', 'UserController::update/$1');

    // Note : Transformer ces actions sensibles en requêtes POST évitera les failles CSRF
    $routes->get('delete/(:num)', 'UserController::delete/$1');
    $routes->get('unban/(:num)', 'UserController::unban/$1');
});

$routes->group('items', ['namespace' => 'App\Controllers\Admin', 'filter' => 'group:superadmin,admin'], static function ($routes): void {
    $routes->get('pending', 'ItemController::pending');

    // Gestion des nouvelles cartes
    $routes->get('approve/(:num)', 'ItemController::approve/$1');
    $routes->get('reject/(:num)', 'ItemController::reject/$1');

    // Drafting (Révisions)
    $routes->get('approve-revision/(:num)', 'ItemController::approveRevision/$1');
    $routes->get('reject-revision/(:num)', 'ItemController::rejectRevision/$1');

    // Maintenance des liens & Suppression administrative
    $routes->get('dead-links', 'ItemController::deadLinks');
    $routes->get('delete/(:num)', 'ItemController::delete/$1');

    // NOUVELLE ROUTE : Remplacement de domaine en masse
    $routes->post('bulk-update-domain', 'ItemController::bulkUpdateDomain');
});

$routes->group('audit', ['namespace' => 'App\Controllers\Admin', 'filter' => 'group:superadmin,admin'], static function ($routes): void {
    $routes->get('/', 'AuditController::index');
});

$routes->group('reports', ['namespace' => 'App\Controllers\Admin', 'filter' => 'group:superadmin,admin'], static function ($routes): void {
    $routes->get('/', 'ReportController::index');
    $routes->get('resolve/(:num)', 'ReportController::resolve/$1');
    $routes->get('delete/(:num)', 'ReportController::delete/$1');
});
// --------------------------------------------------------------------
// Routes par défaut de Shield (Login, Register, etc.)
// --------------------------------------------------------------------
if (class_exists(\CodeIgniter\Shield\Config\Auth::class)) {
    service('auth')->routes($routes);
}