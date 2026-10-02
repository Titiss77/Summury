<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Shield\Config\Auth;

// @var RouteCollection $routes

// Pages publiques (chemins courts)
$routes->get('/', 'HomeController::index');
$routes->get('c/(:num)', 'HomeController::categorie/$1');
$routes->get('l', 'HomeController::legal');
$routes->get('v', 'HomeController::privacy');
$routes->get('t', 'HomeController::cgu');
$routes->get('j', 'CronController::run');
$routes->get('q', 'ItemController::checkDispo');

// Compatibilité avec les anciennes URL publiques.
$routes->get('categorie/(:num)', static fn (string $id) => redirect()->to('c/'.$id));
$routes->get('p/(:num)', static fn (string $id) => redirect()->to('c/'.$id));
$routes->get('legal', static fn () => redirect()->to('l'));
$routes->get('privacy', static fn () => redirect()->to('v'));
$routes->get('cgu', static fn () => redirect()->to('t'));
$routes->get('cron/run', static fn () => redirect()->to('j'));
$routes->get('item/check-dispo', static fn () => redirect()->to('q'));

// Routes protégées par session.
$routes->group('', ['filter' => 'session'], static function (RouteCollection $routes): void {
    $routes->get('m', 'ItemController::form');
    $routes->get('m/(:num)', 'ItemController::form/$1');
    $routes->post('s', 'ItemController::save');
    $routes->get('d/(:num)', 'ItemController::delete/$1');
    $routes->post('e/(:num)', 'ItemController::incrementEpisode/$1');
    $routes->post('n/(:num)', 'ItemController::incrementSaison/$1');
    $routes->post('o', 'ItemController::updateOrder');
    $routes->get('g', 'ItemController::checkToGlobal');
    $routes->get('u/(:num)', 'ItemController::turnToAdmin/$1');
    $routes->get('f', 'MediaSearchController::search');
    $routes->get('r', 'ItemController::viewDeleted');
    $routes->get('rr/(:num)', 'ItemController::restore/$1');
    $routes->get('rd/(:num)', 'ItemController::permanentDelete/$1');
    $routes->get('ra', 'ItemController::restoreAll');
    $routes->get('re', 'ItemController::emptyTrash');
    $routes->get('p', 'ProfileController::index');
    $routes->post('pw', 'ProfileController::updatePassword');

    // Anciennes URL : elles restent utilisables sans redirection des formulaires POST.
    $routes->get('item/form', static fn () => redirect()->to('m'));
    $routes->get('item/form/(:num)', static fn (string $id) => redirect()->to('m/'.$id));
    $routes->post('item/save', 'ItemController::save');
    $routes->get('item/delete/(:num)', static fn (string $id) => redirect()->to('d/'.$id));
    $routes->post('item/increment-episode/(:num)', 'ItemController::incrementEpisode/$1');
    $routes->post('item/increment-saison/(:num)', 'ItemController::incrementSaison/$1');
    $routes->post('items/update-order', 'ItemController::updateOrder');
    $routes->get('items/check-to-global', static fn () => redirect()->to('g'));
    $routes->get('item/turn/(:num)', static fn (string $id) => redirect()->to('u/'.$id));
    $routes->get('item/search', 'MediaSearchController::search');
    $routes->get('items/deleted', static fn () => redirect()->to('r'));
    $routes->get('item/restore/(:num)', static fn (string $id) => redirect()->to('rr/'.$id));
    $routes->get('item/permanent-delete/(:num)', static fn (string $id) => redirect()->to('rd/'.$id));
    $routes->get('items/restore-all', static fn () => redirect()->to('ra'));
    $routes->get('items/empty-trash', static fn () => redirect()->to('re'));
    $routes->get('profile', static fn () => redirect()->to('p'));
    $routes->post('profile/update-password', 'ProfileController::updatePassword');
});

// Administration : filtres de rôle inchangés.
$routes->group('a', ['namespace' => 'App\\Controllers\\Admin', 'filter' => 'group:superadmin,admin'], static function (RouteCollection $routes): void {
    $routes->get('u', 'UserController::index');
    $routes->get('u/(:num)', 'UserController::edit/$1');
    $routes->post('u/(:num)', 'UserController::update/$1');
    $routes->get('u/(:num)/x', 'UserController::delete/$1');
    $routes->get('u/(:num)/b', 'UserController::unban/$1');
    $routes->get('i', 'ItemController::pending');
    $routes->get('i/(:num)', 'ItemController::approve/$1');
    $routes->get('i/(:num)/x', 'ItemController::reject/$1');
    $routes->get('v/(:num)', 'ItemController::approveRevision/$1');
    $routes->get('v/(:num)/x', 'ItemController::rejectRevision/$1');
    $routes->get('d', 'ItemController::deadLinks');
    $routes->get('d/(:num)', 'ItemController::delete/$1');
    $routes->post('b', 'ItemController::bulkUpdateDomain');
    $routes->get('l', 'AuditController::index');
});

// Anciennes URL d'administration.
$routes->group('users', ['namespace' => 'App\\Controllers\\Admin', 'filter' => 'group:superadmin,admin'], static function (RouteCollection $routes): void {
    $routes->get('/', static fn () => redirect()->to('a/u'));
    $routes->get('edit/(:num)', static fn (string $id) => redirect()->to('a/u/'.$id));
    $routes->post('update/(:num)', 'UserController::update/$1');
    $routes->get('delete/(:num)', static fn (string $id) => redirect()->to('a/u/'.$id.'/x'));
    $routes->get('unban/(:num)', static fn (string $id) => redirect()->to('a/u/'.$id.'/b'));
});
$routes->group('items', ['namespace' => 'App\\Controllers\\Admin', 'filter' => 'group:superadmin,admin'], static function (RouteCollection $routes): void {
    $routes->get('pending', static fn () => redirect()->to('a/i'));
    $routes->get('approve/(:num)', static fn (string $id) => redirect()->to('a/i/'.$id));
    $routes->get('reject/(:num)', static fn (string $id) => redirect()->to('a/i/'.$id.'/x'));
    $routes->get('approve-revision/(:num)', static fn (string $id) => redirect()->to('a/v/'.$id));
    $routes->get('reject-revision/(:num)', static fn (string $id) => redirect()->to('a/v/'.$id.'/x'));
    $routes->get('dead-links', static fn () => redirect()->to('a/d'));
    $routes->get('delete/(:num)', static fn (string $id) => redirect()->to('a/d/'.$id));
    $routes->post('bulk-update-domain', 'ItemController::bulkUpdateDomain');
});
$routes->group('audit', ['namespace' => 'App\\Controllers\\Admin', 'filter' => 'group:superadmin,admin'], static function (RouteCollection $routes): void {
    $routes->get('/', static fn () => redirect()->to('a/l'));
});

// Routes Shield raccourcies (les noms restent identiques pour url_to()).
if (class_exists(Auth::class)) {
    service('auth')->routes($routes);
}
$routes->group('/', ['namespace' => 'CodeIgniter\\Shield\\Controllers'], static function (RouteCollection $routes): void {
    $routes->get('register', static fn () => redirect()->to('y'));
    $routes->post('register', 'RegisterController::registerAction');
    $routes->get('login', static fn () => redirect()->to('z'));
    $routes->post('login', 'LoginController::loginAction');
    $routes->get('logout', static fn () => redirect()->to('x'));
    $routes->get('login/magic-link', static fn () => redirect()->to('z/m'));
    $routes->post('login/magic-link', 'MagicLinkController::loginAction');
    $routes->get('login/verify-magic-link', 'MagicLinkController::verify');
    $routes->get('auth/a/show', 'ActionController::show');
    $routes->post('auth/a/handle', 'ActionController::handle');
    $routes->post('auth/a/verify', 'ActionController::verify');
});
