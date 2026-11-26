<?php declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Shield\Config\Auth;

// @var RouteCollection $routes

// Pages publiques (chemins courts)
$routes->get('/', 'HomeController::index');
$routes->get('c/(:num)', 'HomeController::categorie/$1');
$routes->get('l', 'HomeController::legal');
$routes->get('v', 'HomeController::privacy');
$routes->get('t', 'HomeController::cgu');
$routes->get('q', 'ItemController::checkDispo');

// Compatibilité avec les anciennes URL publiques.
$routes->get('categorie/(:num)', static fn(string $id) => redirect()->to('c/' . $id));
$routes->get('p/(:num)', static fn(string $id) => redirect()->to('c/' . $id));
$routes->get('legal', static fn() => redirect()->to('l'));
$routes->get('privacy', static fn() => redirect()->to('v'));
$routes->get('cgu', static fn() => redirect()->to('t'));
$routes->get('item/check-dispo', static fn() => redirect()->to('q'));

// Routes protégées par session.
$routes->group('', ['filter' => 'session'], static function (RouteCollection $routes): void {
    $routes->get('m', 'ItemController::form');
    $routes->get('m/(:num)', 'ItemController::form/$1');
    $routes->post('s', 'ItemController::save');
    $routes->post('d/(:num)', 'ItemController::delete/$1');
    $routes->post('e/(:num)', 'ItemController::incrementEpisode/$1');
    $routes->post('o', 'ItemController::updateOrder');
    $routes->get('g', 'ItemController::checkToGlobal');
    $routes->post('u/(:num)', 'ItemController::turnToAdmin/$1');
    $routes->get('f', 'MediaSearchController::search');
    $routes->get('r', 'ItemController::viewDeleted');
    $routes->post('rr/(:num)', 'ItemController::restore/$1');
    $routes->post('rd/(:num)', 'ItemController::permanentDelete/$1');
    $routes->post('ra', 'ItemController::restoreAll');
    $routes->post('re', 'ItemController::emptyTrash');
    $routes->get('p', 'ProfileController::index');
    $routes->post('pw', 'ProfileController::updatePassword');
    $routes->post('delete-account', 'ProfileController::deleteAccount');
    $routes->post('report-link', 'ItemController::reportLink');

    // Anciennes URL mutatrices conservées en POST pour éviter les opérations via GET.
    $routes->get('item/form', static fn() => redirect()->to('m'));
    $routes->get('item/form/(:num)', static fn(string $id) => redirect()->to('m/' . $id));
    $routes->post('item/save', 'ItemController::save');
    $routes->post('item/delete/(:num)', 'ItemController::delete/$1');
    $routes->post('item/increment-episode/(:num)', 'ItemController::incrementEpisode/$1');
    $routes->post('items/update-order', 'ItemController::updateOrder');
    $routes->get('items/check-to-global', static fn() => redirect()->to('g'));
    $routes->post('item/turn/(:num)', 'ItemController::turnToAdmin/$1');
    $routes->get('item/search', 'MediaSearchController::search');
    $routes->get('items/deleted', static fn() => redirect()->to('r'));
    $routes->post('item/restore/(:num)', 'ItemController::restore/$1');
    $routes->post('item/permanent-delete/(:num)', 'ItemController::permanentDelete/$1');
    $routes->post('items/restore-all', 'ItemController::restoreAll');
    $routes->post('items/empty-trash', 'ItemController::emptyTrash');
    $routes->get('profile', static fn() => redirect()->to('p'));
    $routes->post('profile/update-password', 'ProfileController::updatePassword');
    $routes->post('update-email', 'ProfileController::updateEmail');
});

// Administration : toutes les opérations qui changent l'état utilisent POST.
$routes->group('a', ['namespace' => 'App\\Controllers\\Admin', 'filter' => 'group:superadmin,admin'], static function (RouteCollection $routes): void {
    $routes->get('u', 'UserController::index');
    $routes->get('u/(:num)', 'UserController::edit/$1');
    $routes->post('u/(:num)', 'UserController::update/$1');
    $routes->post('u/(:num)/x', 'UserController::delete/$1');
    $routes->post('u/(:num)/b', 'UserController::unban/$1');
    $routes->get('i', 'ItemController::pending');
    $routes->post('i/(:num)', 'ItemController::approve/$1');
    $routes->post('i/(:num)/x', 'ItemController::reject/$1');
    $routes->post('v/(:num)', 'ItemController::approveRevision/$1');
    $routes->post('v/(:num)/x', 'ItemController::rejectRevision/$1');
    $routes->get('d', 'ItemController::deadLinks');
    $routes->post('d/report/(:num)/approve', 'ItemController::approveUserLinkReport/$1');
    $routes->post('d/report/(:num)/reject', 'ItemController::rejectUserLinkReport/$1');
    $routes->post('d/valid/(:num)/dead', 'ItemController::declareValidDomainDead/$1');
    $routes->post('d/(:num)', 'ItemController::delete/$1');
    $routes->post('b', 'ItemController::bulkUpdateDomain');
    $routes->get('l', 'AuditController::index');
    $routes->get('fnd', 'ItemController::falseNegativeDomain');
});

// Anciennes URL d'administration.
$routes->group('users', ['namespace' => 'App\\Controllers\\Admin', 'filter' => 'group:superadmin,admin'], static function (RouteCollection $routes): void {
    $routes->get('/', static fn() => redirect()->to('a/u'));
    $routes->get('edit/(:num)', static fn(string $id) => redirect()->to('a/u/' . $id));
    $routes->post('update/(:num)', 'UserController::update/$1');
    $routes->post('delete/(:num)', 'UserController::delete/$1');
    $routes->post('unban/(:num)', 'UserController::unban/$1');
});
$routes->group('items', ['namespace' => 'App\\Controllers\\Admin', 'filter' => 'group:superadmin,admin'], static function (RouteCollection $routes): void {
    $routes->get('pending', static fn() => redirect()->to('a/i'));
    $routes->post('approve/(:num)', 'ItemController::approve/$1');
    $routes->post('reject/(:num)', 'ItemController::reject/$1');
    $routes->post('approve-revision/(:num)', 'ItemController::approveRevision/$1');
    $routes->post('reject-revision/(:num)', 'ItemController::rejectRevision/$1');
    $routes->get('dead-links', static fn() => redirect()->to('a/d'));
    $routes->post('delete/(:num)', 'ItemController::delete/$1');
    $routes->post('bulk-update-domain', 'ItemController::bulkUpdateDomain');
    $routes->post('valid-domain/(:num)/dead', 'ItemController::declareValidDomainDead/$1');
});
$routes->group('audit', ['namespace' => 'App\\Controllers\\Admin', 'filter' => 'group:superadmin,admin'], static function (RouteCollection $routes): void {
    $routes->get('/', static fn() => redirect()->to('a/l'));
});

// Routes Shield raccourcies
if (class_exists(Auth::class)) {
    service('auth')->routes($routes);
}
$routes->group('/', ['namespace' => 'CodeIgniter\\Shield\\Controllers'], static function (RouteCollection $routes): void {
    $routes->get('register', static fn() => redirect()->to('y'));
    $routes->post('register', 'RegisterController::registerAction');
    $routes->get('login', static fn() => redirect()->to('z'));
    $routes->post('login', 'LoginController::loginAction');
    $routes->get('logout', static fn() => redirect()->to('x'));
    $routes->get('login/magic-link', static fn() => redirect()->to('z/m'));
    $routes->post('login/magic-link', 'MagicLinkController::loginAction');
    $routes->get('login/verify-magic-link', 'MagicLinkController::verify');
    $routes->get('auth/a/show', 'ActionController::show');
    $routes->post('auth/a/handle', 'ActionController::handle');
    $routes->post('auth/a/verify', 'ActionController::verify');
});

// Récupération de mot de passe
$routes->group('forgot-password', ['namespace' => 'App\\Controllers'], static function ($routes) {
    $routes->get('/', 'PasswordResetController::forgotPasswordView', ['as' => 'forgot-password']);
    $routes->post('/', 'PasswordResetController::sendCode');
    $routes->get('verify', 'PasswordResetController::verifyCodeView', ['as' => 'verify-reset-code']);
    $routes->post('verify', 'PasswordResetController::verifyCode');
    $routes->get('reset', 'PasswordResetController::resetPasswordView', ['as' => 'reset-password']);
    $routes->post('reset', 'PasswordResetController::updatePassword');
});

// Configuration de scraping (Super Admin)
$routes->group('a/sc', ['filter' => 'group:superadmin'], static function ($routes) {
    $routes->get('/', 'Admin\\SiteConfigController::index');
    $routes->get('new', 'Admin\\SiteConfigController::form');
    $routes->get('(:num)', 'Admin\\SiteConfigController::form/$1');
    $routes->post('save', 'Admin\\SiteConfigController::save');
    $routes->post('delete/(:num)', 'Admin\\SiteConfigController::delete/$1');
});
