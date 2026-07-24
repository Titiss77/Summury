<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Session;

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\I18n\Time;
use Config\Cookie as CookieConfig;
use Config\Session as SessionConfig;
use Psr\Log\LoggerAwareTrait;

/**
 * Implementation of CodeIgniter session container.
 *
 * Session configuration is done through session variables and cookie related
 * variables in app/config/App.php
 *
 * @property string $session_id
 *
 * @see SessionTest
 */
class Session implements SessionInterface
{
    use LoggerAwareTrait;

    /**
     * Instance of the driver to use.
     *
     * @var \SessionHandlerInterface
     */
    protected $driver;

    /**
     * The storage driver to use: files, database, redis, memcached.
     *
     * @var string
     *
     * @deprecated use $this->config->driver
     */
    protected $sessionDriverName;

    /**
     * The session cookie name, must contain only [0-9a-z_-] characters.
     *
     * @var string
     *
     * @deprecated use $this->config->cookieName
     */
    protected $sessionCookieName = 'ci_session';

    /**
     * The number of SECONDS you want the session to last.
     * Setting it to 0 (zero) means expire when the browser is closed.
     *
     * @var int
     *
     * @deprecated use $this->config->expiration
     */
    protected $sessionExpiration = 7200;

    /**
     * The location to save sessions to, driver dependent.
     *
     * For the 'files' driver, it's a path to a writable directory.
     * WARNING: Only absolute paths are supported!
     *
     * For the 'database' driver, it's a table name.
     *
     * @todo address memcache & redis needs
     *
     * IMPORTANT: You are REQUIRED to set a valid save path!
     *
     * @var string
     *
     * @deprecated use $this->config->savePath
     */
    protected $sessionSavePath;

    /**
     * Whether to match the user's IP address when reading the session data.
     *
     * WARNING: If you're using the database driver, don't forget to update
     * your session table's PRIMARY KEY when changing this setting.
     *
     * @var bool
     *
     * @deprecated use $this->config->matchIP
     */
    protected $sessionMatchIP = false;

    /**
     * How many seconds between CI regenerating the session ID.
     *
     * @var int
     *
     * @deprecated use $this->config->timeToUpdate
     */
    protected $sessionTimeToUpdate = 300;

    /**
     * Whether to destroy session data associated with the old session ID
     * when auto-regenerating the session ID. When set to FALSE, the data
     * will be later deleted by the garbage collector.
     *
     * @var bool
     *
     * @deprecated use $this->config->regenerateDestroy
     */
    protected $sessionRegenerateDestroy = false;

    /**
     * The session cookie instance.
     *
     * @var Cookie
     */
    protected $cookie;

    /**
     * The domain name to use for cookies.
     * Set to .your-domain.com for site-wide cookies.
     *
     * @var string
     *
     * @deprecated no longer used
     */
    protected $cookieDomain = '';

    /**
     * Path used for storing cookies.
     * Typically will be a forward slash.
     *
     * @var string
     *
     * @deprecated no longer used
     */
    protected $cookiePath = '/';

    /**
     * Cookie will only be set if a secure HTTPS connection exists.
     *
     * @var bool
     *
     * @deprecated no longer used
     */
    protected $cookieSecure = false;

    /**
     * Cookie SameSite setting as described in RFC6265
     * Must be 'None', 'Lax' or 'Strict'.
     *
     * @var string
     *
     * @deprecated no longer used
     */
    protected $cookieSameSite = Cookie::SAMESITE_LAX;

    /**
     * sid regex expression.
     *
     * @var string
     */
    protected $sidRegexp;

    /**
     * Session Config.
     */
    protected SessionConfig $config;

    /**
     * Constructor.
     *
     * Extract configuration settings and save them here.
     */
    public function __construct(\SessionHandlerInterface $driver, SessionConfig $config)
    {
        $this->driver = $driver;

        $this->config = $config;

        $cookie = config(CookieConfig::class);

        $this->cookie = (new Cookie($this->config->cookieName, '', [
            'expires' => 0 === $this->config->expiration ? 0 : Time::now()->getTimestamp() + $this->config->expiration,
            'path' => $cookie->path,
            'domain' => $cookie->domain,
            'secure' => $cookie->secure,
            'httponly' => true, // for security
            'samesite' => $cookie->samesite ?? Cookie::SAMESITE_LAX,
            'raw' => $cookie->raw ?? false,
        ]))->withPrefix(''); // Cookie prefix should be ignored.

        helper('array');
    }

    /**
     * Magic method to set variables in the session by simply calling
     *  $session->foo = bar;.
     *
     * @param string $key   identifier of the session property to set
     * @param mixed  $value
     */
    public function __set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Magic method to get session variables by simply calling
     *  $foo = $session->foo;.
     *
     * @param string $key identifier of the session property to remove
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        // Note: Keep this order the same, just in case somebody wants to
        // use 'session_id' as a session data key, for whatever reason
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        if ('session_id' === $key) {
            return session_id();
        }

        return null;
    }

    /**
     * Magic method to check for session variables.
     *
     * Different from `has()` in that it will validate 'session_id' as well.
     * Mostly used by internal PHP functions, users should stick to `has()`.
     *
     * @param string $key identifier of the session property to remove
     */
    public function __isset(string $key): bool
    {
        return isset($_SESSION[$key]) || 'session_id' === $key;
    }

    /**
     * Initialize the session container and starts up the session.
     *
     * @return null|$this
     */
    public function start()
    {
        if (is_cli() && ENVIRONMENT !== 'testing') {
            // @codeCoverageIgnoreStart
            $this->logger->debug('Session: Initialization under CLI aborted.');

            return null;
            // @codeCoverageIgnoreEnd
        }

        if ((bool) ini_get('session.auto_start')) {
            $this->logger->error('Session: session.auto_start is enabled in php.ini. Aborting.');

            return null;
        }

        if (PHP_SESSION_ACTIVE === session_status()) {
            $this->logger->warning('Session: Sessions is enabled, and one exists. Please don\'t $session->start();');

            return null;
        }

        $this->configure();
        $this->setSaveHandler();

        // Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
        if (
            isset($_COOKIE[$this->config->cookieName])
            && (!is_string($_COOKIE[$this->config->cookieName]) || 1 !== preg_match('#\A'.$this->sidRegexp.'\z#', $_COOKIE[$this->config->cookieName]))
        ) {
            unset($_COOKIE[$this->config->cookieName]);
        }

        $this->startSession();

        // Is session ID auto-regeneration configured? (ignoring ajax requests)
        if ((!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 'xmlhttprequest' !== strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
            && ($regenerateTime = $this->config->timeToUpdate) > 0
        ) {
            if (!isset($_SESSION['__ci_last_regenerate'])) {
                $_SESSION['__ci_last_regenerate'] = Time::now()->getTimestamp();
            } elseif ($_SESSION['__ci_last_regenerate'] < (Time::now()->getTimestamp() - $regenerateTime)) {
                $this->regenerate($this->config->regenerateDestroy);
            }
        }
        // Another work-around ... PHP doesn't seem to send the session cookie
        // unless it is being currently created or regenerated
        elseif (isset($_COOKIE[$this->config->cookieName]) && $_COOKIE[$this->config->cookieName] === session_id()) {
            $this->setCookie();
        }

        $this->initVars();
        $this->logger->debug("Session: Class initialized using '".$this->config->driver."' driver.");

        return $this;
    }

    /**
     * Destroys the current session.
     *
     * @deprecated use destroy() instead
     */
    public function stop(): void
    {
        $this->destroy();
    }

    /**
     * Regenerates the session ID.
     *
     * @param bool $destroy Should old session data be destroyed?
     */
    public function regenerate(bool $destroy = false): void
    {
        $_SESSION['__ci_last_regenerate'] = Time::now()->getTimestamp();
        session_regenerate_id($destroy);

        $this->removeOldSessionCookie();
    }

    /**
     * Destroys the current session.
     */
    public function destroy(): void
    {
        if (ENVIRONMENT === 'testing') {
            return;
        }

        session_destroy();
    }

    /**
     * Writes session data and close the current session.
     */
    public function close(): void
    {
        if (ENVIRONMENT === 'testing') {
            return;
        }

        session_write_close();
    }

    /**
     * Sets user data into the session.
     *
     * If $data is a string, then it is interpreted as a session property
     * key, and  $value is expected to be non-null.
     *
     * If $data is an array, it is expected to be an array of key/value pairs
     * to be set as session properties.
     *
     * @param array<string, mixed>|list<string>|string $data  Property name or associative array of properties
     * @param mixed                                    $value Property value if single key provided
     */
    public function set($data, $value = null): void
    {
        $data = is_array($data) ? $data : [$data => $value];

        if (array_is_list($data)) {
            $data = array_fill_keys($data, null);
        }

        foreach ($data as $sessionKey => $sessionValue) {
            $_SESSION[$sessionKey] = $sessionValue;
        }
    }

    /**
     * Get user data that has been set in the session.
     *
     * If the property exists as "normal", returns it.
     * Otherwise, returns an array of any temp or flash data values with the
     * property key.
     *
     * Replaces the legacy method $session->userdata();
     *
     * @param null|string $key Identifier of the session property to retrieve
     *
     * @return ($key is string ? mixed : array<string, mixed>)
     */
    public function get(?string $key = null)
    {
        if (!isset($_SESSION) || [] === $_SESSION) {
            return null === $key ? [] : null;
        }

        $key ??= '';

        if ('' !== $key) {
            return $_SESSION[$key] ?? dot_array_search($key, $_SESSION);
        }

        $userdata = [];
        $exclude = array_merge(['__ci_vars'], $this->getFlashKeys(), $this->getTempKeys());

        foreach (array_keys($_SESSION) as $key) {
            if (!in_array($key, $exclude, true)) {
                $userdata[$key] = $_SESSION[$key];
            }
        }

        return $userdata;
    }

    /**
     * Returns whether an index exists in the session array.
     *
     * @param string $key identifier of the session property we are interested in
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Push new value onto session value that is array.
     *
     * @param string               $key  identifier of the session property we are interested in
     * @param array<string, mixed> $data value to be pushed to existing session key
     */
    public function push(string $key, array $data): void
    {
        if ($this->has($key) && is_array($value = $this->get($key))) {
            $this->set($key, array_merge($value, $data));
        }
    }

    /**
     * Remove one or more session properties.
     *
     * If $key is an array, it is interpreted as an array of string property
     * identifiers to remove. Otherwise, it is interpreted as the identifier
     * of a specific session property to remove.
     *
     * @param list<string>|string $key identifier of the session property or properties to remove
     */
    public function remove($key): void
    {
        $key = is_array($key) ? $key : [$key];

        foreach ($key as $k) {
            unset($_SESSION[$k]);
        }
    }

    /**
     * Sets data into the session that will only last for a single request.
     * Perfect for use with single-use status update messages.
     *
     * If $data is an array, it is interpreted as an associative array of
     * key/value pairs for flashdata properties.
     * Otherwise, it is interpreted as the identifier of a specific
     * flashdata property, with $value containing the property value.
     *
     * @param array<string, mixed>|string $data  Property identifier or associative array of properties
     * @param mixed                       $value Property value if $data is a scalar
     */
    public function setFlashdata($data, $value = null): void
    {
        $this->set($data, $value);
        $this->markAsFlashdata(is_array($data) ? array_keys($data) : $data);
    }

    /**
     * Retrieve one or more items of flash data from the session.
     *
     * If the item key is null, return all flashdata.
     *
     * @param null|string $key Property identifier
     *
     * @return ($key is string ? mixed : array<string, mixed>)
     */
    public function getFlashdata(?string $key = null)
    {
        $_SESSION['__ci_vars'] ??= [];

        if (isset($key)) {
            if (!isset($_SESSION['__ci_vars'][$key]) || is_int($_SESSION['__ci_vars'][$key])) {
                return null;
            }

            return $_SESSION[$key] ?? null;
        }

        $flashdata = [];

        foreach ($_SESSION['__ci_vars'] as $key => $value) {
            if (!is_int($value)) {
                $flashdata[$key] = $_SESSION[$key];
            }
        }

        return $flashdata;
    }

    /**
     * Keeps a single piece of flash data alive for one more request.
     *
     * @param list<string>|string $key Property identifier or array of them
     */
    public function keepFlashdata($key): void
    {
        $this->markAsFlashdata($key);
    }

    /**
     * Mark a session property or properties as flashdata. This returns
     * `false` if any of the properties were not already set.
     *
     * @param list<string>|string $key Property identifier or array of them
     */
    public function markAsFlashdata($key): bool
    {
        $keys = is_array($key) ? $key : [$key];

        foreach ($keys as $sessionKey) {
            if (!isset($_SESSION[$sessionKey])) {
                return false;
            }
        }

        $_SESSION['__ci_vars'] ??= [];
        $_SESSION['__ci_vars'] = [...$_SESSION['__ci_vars'], ...array_fill_keys($keys, 'new')];

        return true;
    }

    /**
     * Unmark data in the session as flashdata.
     *
     * @param list<string>|string $key Property identifier or array of them
     */
    public function unmarkFlashdata($key): void
    {
        if (!isset($_SESSION['__ci_vars'])) {
            return;
        }

        if (!is_array($key)) {
            $key = [$key];
        }

        foreach ($key as $k) {
            if (isset($_SESSION['__ci_vars'][$k]) && !is_int($_SESSION['__ci_vars'][$k])) {
                unset($_SESSION['__ci_vars'][$k]);
            }
        }

        if ([] === $_SESSION['__ci_vars']) {
            unset($_SESSION['__ci_vars']);
        }
    }

    /**
     * Retrieve all of the keys for session data marked as flashdata.
     *
     * @return list<string>
     */
    public function getFlashKeys(): array
    {
        if (!isset($_SESSION['__ci_vars'])) {
            return [];
        }

        $keys = [];

        foreach (array_keys($_SESSION['__ci_vars']) as $key) {
            if (!is_int($_SESSION['__ci_vars'][$key])) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Sets new data into the session, and marks it as temporary data
     * with a set lifespan.
     *
     * @param array<string, mixed>|list<string>|string $data  Session data key or associative array of items
     * @param mixed                                    $value Value to store
     * @param int                                      $ttl   Time-to-live in seconds
     */
    public function setTempdata($data, $value = null, int $ttl = 300): void
    {
        $this->set($data, $value);
        $this->markAsTempdata($data, $ttl);
    }

    /**
     * Returns either a single piece of tempdata, or all temp data currently
     * in the session.
     *
     * @param null|string $key Session data key
     *
     * @return ($key is string ? mixed : array<string, mixed>)
     */
    public function getTempdata(?string $key = null)
    {
        $_SESSION['__ci_vars'] ??= [];

        if (isset($key)) {
            if (!isset($_SESSION['__ci_vars'][$key]) || !is_int($_SESSION['__ci_vars'][$key])) {
                return null;
            }

            return $_SESSION[$key] ?? null;
        }

        $tempdata = [];

        foreach ($_SESSION['__ci_vars'] as $key => $value) {
            if (is_int($value)) {
                $tempdata[$key] = $_SESSION[$key];
            }
        }

        return $tempdata;
    }

    /**
     * Removes a single piece of temporary data from the session.
     *
     * @param string $key Session data key
     */
    public function removeTempdata(string $key): void
    {
        $this->unmarkTempdata($key);
        unset($_SESSION[$key]);
    }

    /**
     * Mark one of more pieces of data as being temporary, meaning that
     * it has a set lifespan within the session.
     *
     * Returns `false` if any of the properties were not set.
     *
     * @param array<string, mixed>|list<string>|string $key Property identifier or array of them
     * @param int                                      $ttl Time to live, in seconds
     */
    public function markAsTempdata($key, int $ttl = 300): bool
    {
        $time = Time::now()->getTimestamp();
        $keys = is_array($key) ? $key : [$key];

        if (array_is_list($keys)) {
            $keys = array_fill_keys($keys, $ttl);
        }

        $tempdata = [];

        foreach ($keys as $sessionKey => $timeToLive) {
            if (!array_key_exists($sessionKey, $_SESSION)) {
                return false;
            }

            if (is_int($timeToLive)) {
                $timeToLive += $time;
            } else {
                $timeToLive = $time + $ttl;
            }

            $tempdata[$sessionKey] = $timeToLive;
        }

        $_SESSION['__ci_vars'] ??= [];
        $_SESSION['__ci_vars'] = [...$_SESSION['__ci_vars'], ...$tempdata];

        return true;
    }

    /**
     * Unmarks temporary data in the session, effectively removing its
     * lifespan and allowing it to live as long as the session does.
     *
     * @param list<string>|string $key Property identifier or array of them
     */
    public function unmarkTempdata($key): void
    {
        if (!isset($_SESSION['__ci_vars'])) {
            return;
        }

        if (!is_array($key)) {
            $key = [$key];
        }

        foreach ($key as $k) {
            if (isset($_SESSION['__ci_vars'][$k]) && is_int($_SESSION['__ci_vars'][$k])) {
                unset($_SESSION['__ci_vars'][$k]);
            }
        }

        if ([] === $_SESSION['__ci_vars']) {
            unset($_SESSION['__ci_vars']);
        }
    }

    /**
     * Retrieve the keys of all session data that have been marked as temporary data.
     *
     * @return list<string>
     */
    public function getTempKeys(): array
    {
        if (!isset($_SESSION['__ci_vars'])) {
            return [];
        }

        $keys = [];

        foreach (array_keys($_SESSION['__ci_vars']) as $key) {
            if (is_int($_SESSION['__ci_vars'][$key])) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Configuration.
     *
     * Handle input binds and configuration defaults.
     */
    protected function configure(): void
    {
        ini_set('session.name', $this->config->cookieName);

        $sameSite = $this->cookie->getSameSite() ?: ucfirst(Cookie::SAMESITE_LAX);

        $params = [
            'lifetime' => $this->config->expiration,
            'path' => $this->cookie->getPath(),
            'domain' => $this->cookie->getDomain(),
            'secure' => $this->cookie->isSecure(),
            'httponly' => true, // HTTP only; Yes, this is intentional and not configurable for security reasons.
            'samesite' => $sameSite,
        ];

        ini_set('session.cookie_samesite', $sameSite);
        session_set_cookie_params($params);

        if ($this->config->expiration > 0) {
            ini_set('session.gc_maxlifetime', (string) $this->config->expiration);
        }

        if ('' !== $this->config->savePath) {
            ini_set('session.save_path', $this->config->savePath);
        }

        // Security is king
        ini_set('session.use_trans_sid', '0');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');

        $this->configureSidLength();
    }

    /**
     * Configure session ID length.
     *
     * To make life easier, we force the PHP defaults. Because PHP9 forces them.
     * See https://wiki.php.net/rfc/deprecations_php_8_4#sessionsid_length_and_sessionsid_bits_per_character
     */
    protected function configureSidLength(): void
    {
        $bitsPerCharacter = (int) ini_get('session.sid_bits_per_character');
        $sidLength = (int) ini_get('session.sid_length');

        // We force the PHP defaults.
        if (PHP_VERSION_ID < 90000) {
            if (4 !== $bitsPerCharacter) {
                ini_set('session.sid_bits_per_character', '4');
            }
            if (32 !== $sidLength) {
                ini_set('session.sid_length', '32');
            }
        }

        $this->sidRegexp = '[0-9a-f]{32}';
    }

    /**
     * Handle temporary variables.
     *
     * Clears old "flash" data, marks the new one for deletion and handles
     * "temp" data deletion.
     */
    protected function initVars(): void
    {
        if (!isset($_SESSION['__ci_vars'])) {
            return;
        }

        $currentTime = Time::now()->getTimestamp();

        foreach ($_SESSION['__ci_vars'] as $key => &$value) {
            if ('new' === $value) {
                $_SESSION['__ci_vars'][$key] = 'old';
            }
            // DO NOT move this above the 'new' check!
            elseif ('old' === $value || $value < $currentTime) {
                unset($_SESSION[$key], $_SESSION['__ci_vars'][$key]);
            }
        }

        if ([] === $_SESSION['__ci_vars']) {
            unset($_SESSION['__ci_vars']);
        }
    }

    /**
     * Sets the driver as the session handler in PHP.
     * Extracted for easier testing.
     */
    protected function setSaveHandler(): void
    {
        session_set_save_handler($this->driver, true);
    }

    /**
     * Starts the session.
     * Extracted for testing reasons.
     */
    protected function startSession(): void
    {
        if (ENVIRONMENT === 'testing') {
            $_SESSION = [];

            return;
        }

        session_start(); // @codeCoverageIgnore
    }

    /**
     * Takes care of setting the cookie on the client side.
     *
     * @codeCoverageIgnore
     */
    protected function setCookie(): void
    {
        $expiration = 0 === $this->config->expiration ? 0 : Time::now()->getTimestamp() + $this->config->expiration;
        $this->cookie = $this->cookie->withValue(session_id())->withExpires($expiration);

        $response = service('response');
        $response->setCookie($this->cookie);
    }

    private function removeOldSessionCookie(): void
    {
        $response = service('response');
        $cookieStoreInResponse = $response->getCookieStore();

        if (!$cookieStoreInResponse->has($this->config->cookieName)) {
            return;
        }

        // CookieStore is immutable.
        $newCookieStore = $cookieStoreInResponse->remove($this->config->cookieName);

        // But clear() method clears cookies in the object (not immutable).
        $cookieStoreInResponse->clear();

        foreach ($newCookieStore as $cookie) {
            $response->setCookie($cookie);
        }
    }
}
