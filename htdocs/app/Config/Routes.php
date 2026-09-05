<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Shield\Config\Auth;

// @var RouteCollection $routes

// --------------------------------------------------------------------
// Pages publiques (accessibles sans être connecté)
// --------------------------------------------------------------------
$routes->get('/', 'HomeController::index');
$routes->get('categorie/(:num)', 'HomeController::categorie/$1');

$routes->get('legal', 'HomeController::legal');
$routes->get('privacy', 'HomeController::privacy');

// Route silencieuse pour la tâche de fond (Pseudo-Cron)
$routes->get('cron/run', 'CronController::run');

// --------------------------------------------------------------------
// Routes protégées par session (Utilisateurs connectés normaux)
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'session'], static function ($routes): void {
    $routes->get('item/form', 'ItemController::form');
    $routes->get('item/form/(:num)', 'ItemController::form/$1');
    $routes->post('item/save', 'ItemController::save');
    $routes->get('item/delete/(:num)', 'ItemController::delete/$1');
    $routes->post('item/increment-episode/(:num)', 'ItemController::incrementEpisode/$1');
    $routes->post('item/increment-saison/(:num)', 'ItemController::incrementSaison/$1');
    $routes->post('items/update-order', 'ItemController::updateOrder');
    $routes->get('items/check-to-global', 'ItemController::checkToGlobal');
    $routes->get('item/turn/(:num)', 'ItemController::turnToAdmin/$1');
    $routes->get('item/search', 'ItemController::search');
    $routes->get('item/check-dispo', 'ItemController::checkDispo');
    $routes->get('items/deleted', 'ItemController::viewDeleted');
    
    // Nouvelles routes pour gérer la corbeille
    $routes->get('item/restore/(:num)', 'ItemController::restore/$1');
    $routes->get('item/permanent-delete/(:num)', 'ItemController::permanentDelete/$1');

    // NOUVELLES ROUTES : Profil utilisateur
    $routes->get('profile', 'ProfileController::index');
    $routes->post('profile/update-password', 'ProfileController::updatePassword');
});

// --------------------------------------------------------------------
// Routes d'Administration (Restreintes par Session ET Rôle Shield)
// --------------------------------------------------------------------
$routes->group('users', ['namespace' => 'App\Controllers\Admin', 'filter' => 'group:superadmin,admin'], static function ($routes): void {
    $routes->get('/', 'UserController::index');
    $routes->get('edit/(:num)', 'UserController::edit/$1');
    $routes->post('update/(:num)', 'UserController::update/$1');
    $routes->get('delete/(:num)', 'UserController::delete/$1');
    $routes->get('unban/(:num)', 'UserController::unban/$1');
});

$routes->group('items', ['namespace' => 'App\Controllers\Admin', 'filter' => 'group:superadmin,admin'], static function ($routes): void {
    $routes->get('pending', 'ItemController::pending');
    $routes->get('approve/(:num)', 'ItemController::approve/$1');
    $routes->get('reject/(:num)', 'ItemController::reject/$1');
    $routes->get('approve-revision/(:num)', 'ItemController::approveRevision/$1');
    $routes->get('reject-revision/(:num)', 'ItemController::rejectRevision/$1');
    $routes->get('dead-links', 'ItemController::deadLinks');
    $routes->get('delete/(:num)', 'ItemController::delete/$1');
    $routes->post('bulk-update-domain', 'ItemController::bulkUpdateDomain');
});

$routes->group('audit', ['namespace' => 'App\Controllers\Admin', 'filter' => 'group:superadmin,admin'], static function ($routes): void {
    $routes->get('/', 'AuditController::index');
});

// --------------------------------------------------------------------
// Routes par défaut de Shield (Login, Register, etc.)
// --------------------------------------------------------------------
if (class_exists(Auth::class)) {
    service('auth')->routes($routes);
}