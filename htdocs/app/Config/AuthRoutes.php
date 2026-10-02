<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AuthRoutes extends BaseConfig
{
    public array $routes = [
        'register' => [
            ['get', 'y', 'RegisterController::registerView', 'register'],
            ['post', 'y', 'RegisterController::registerAction'],
        ],
        'login' => [
            ['get', 'z', 'LoginController::loginView', 'login'],
            ['post', 'z', 'LoginController::loginAction'],
        ],
        'magic-link' => [
            ['get', 'z/m', 'MagicLinkController::loginView', 'magic-link'],
            ['post', 'z/m', 'MagicLinkController::loginAction'],
            ['get', 'z/v', 'MagicLinkController::verify', 'verify-magic-link'],
        ],
        'logout' => [
            ['get', 'x', 'LoginController::logoutAction', 'logout'],
        ],
        'auth-actions' => [
            ['get', 'z/a/s', 'ActionController::show', 'auth-action-show'],
            ['post', 'z/a/h', 'ActionController::handle', 'auth-action-handle'],
            ['post', 'z/a/v', 'ActionController::verify', 'auth-action-verify'],
        ],
    ];
}
