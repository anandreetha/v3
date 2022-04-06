<?php

use Multiple\Core\Misc\RedisSentinel;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Logger\Factory;
use Phalcon\Mvc\Url;
use HTMLPurifier_Config as Config;
use Phalcon\Cache\Backend\Redis;
use Phalcon\Cache\Frontend\Data as FrontData;

// Inject config
$di->setShared(
    'config',
    function () {
        return new ConfigIni(APP_PATH . 'config/config.ini');
    }
);

// Inject build data
$di->setShared(
    'build',
    function () {
        return new ConfigIni(APP_PATH . 'build/build.ini');
    }
);

// Inject base url
$di->setShared(
    'url',
    function () {

        $config = $this->get('config');

        $url = new Url();

        $url->setBaseUri($config->general->baseUri);

        return $url;
    }
);

// Inject any custom routes
$di->setShared(
    'router',
    function () {
        return include APP_PATH . 'config/routes.php';
    }
);

// Inject logger service
$di->setShared(
    'logger',
    function () {
        $options = [
            'name' => APP_PATH.'logs/v3-application.log',
            'adapter' => 'file',
        ];

        $logger = Factory::load($options);

        $config = $this->get('config');
        $logLevel = strtoupper($config->general->logLevel);

        switch ($logLevel) {
            case 'DEBUG':
                $phalconLogLevel = \Phalcon\Logger::DEBUG;
                break;
            case 'INFO':
                $phalconLogLevel = \Phalcon\Logger::INFO;
                break;
            case 'NOTICE':
                $phalconLogLevel = \Phalcon\Logger::NOTICE;
                break;
            case 'WARNING':
                $phalconLogLevel = \Phalcon\Logger::WARNING;
                break;
            case 'ERROR':
                $phalconLogLevel = \Phalcon\Logger::ERROR;
                break;
            case 'ALERT':
                $phalconLogLevel = \Phalcon\Logger::ALERT;
                break;
            case 'CRITICAL':
                $phalconLogLevel = \Phalcon\Logger::CRITICAL;
                break;
            case 'EMERGENCY':
                $phalconLogLevel = \Phalcon\Logger::EMERGENCY;
                break;
            default:
                $phalconLogLevel = \Phalcon\Logger::DEBUG;
                break;
        }

        $logger->setLogLevel(
            $phalconLogLevel
        );


        return $logger;
    }
);

// Setup the main database service
$di->setShared(
    'db',
    function () {
        $config = $this->get('config');
        return new DbAdapter(
            [
                'host' => $config->databaseV3->host,
                'username' => $config->databaseV3->username,
                'password' => $config->databaseV3->password,
                'dbname' => $config->databaseV3->dbname,
            ]
        );
    }
);

// Setup and inject Mongo as a service
$di->setShared(
    'mongo',
    function () {
        $config = $this->get('config');

        if (!$config->mongo->username || !$config->mongo->password) {
            $dsn = 'mongodb://' . $config->mongo->host;
        } else {
            $dsn = sprintf(
                'mongodb://%s:%s@%s',
                $config->mongo->username,
                $config->mongo->password,
                $config->mongo->host
            );
        }

        $mongo = new \MongoDB\Client($dsn);

        return $mongo->selectDatabase($config->mongo->dbname);
    }
);

// Setup and inject HtmlPurifier as a service
$di->setShared(
    "purifier",
    function () {
        $config = Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);

        // For allowing certain html and other elements such e.g forms, js etc
        $config->set('HTML.Trusted', true);
        $config->set('Attr.EnableID', true);

        // Allow iframes
        $config->set('HTML.SafeIframe', 'true');
        $config->set('URI.SafeIframeRegexp', '%^(https?:)%');

        $def = $config->getHTMLDefinition(true);

        // Allow iframe attributes
        $def->addAttribute('iframe', 'align', 'Text');
        $def->addAttribute('iframe', 'name', 'Text');

        // Allow form attributes
        $def->addElement(
            'form',   // name
            'Block',  // content set
            'Flow', // allowed children
            'Common', // attribute collection
            array( // attributes
                'action*' => 'URI',
                'method' => 'Enum#get|post',
                'name' => 'ID'
            )
        );
        $def->addAttribute('form', 'method', 'Text');
        $def->addAttribute('form', 'name', 'Text');
        $def->addAttribute('form', 'target', 'Text');

        // Allow link attributes
        $def->addAttribute('a', 'target', 'Text');

        // Allow div attributes
        $def->addAttribute('div', 'id', 'Text');

        $purifier = new \HTMLPurifier($config);

        return $purifier;
    }
);

// Setup and inject the guzzle REST Client as a service
$di->setShared(
    'client',

    function () {
        $config = $this->get('config');

        $defaultHeaders = [
            'Content-Type' => $config->bniApi->contentType,
            'Authorization' => $config->bniApi->authKey
        ];


        $defaultOptions = [
            'base_uri' => $config->bniApi->internalApiBrokerUrl,
            'timeout' => $config->bniApi->timeout,
            'headers' => $defaultHeaders
        ];

        $client = new \GuzzleHttp\Client($defaultOptions);

        return $client;
    }
);

/**
 * Maps "hostname:portnum" -> [ host=>hostname, port=>portnum ]
 *
 * @param $host
 * @return array
 */
function parseHost($host)
{
    $hostAndPort = explode(':', $host, 2);

    $hostname = $hostAndPort[0];
    $port = null;
    if (count($hostAndPort) > 1) {
        $port = intval($hostAndPort[1]);
    }

    return array(
        'host' => $hostname,
        'port' => $port
    );
}

/**
 * Maps "host:port,host2:port2" -> [ [ host=>host, port=>port ], [ host=>host2, port=>port2 ] ]
 *
 * @param $hosts
 * @return array
 */
function parseHosts($hosts) {
    $hosts = explode(',', $hosts);
    $hosts_parsed = array();
    foreach ($hosts as $host) {
        array_push($hosts_parsed, parseHost($host));
    }

    return $hosts_parsed;
}

// Setup and inject the Redis cache as a service
$di->setShared('redisCache', function () {

    $config = $this->get('config');

    $redis_lifetime = 3600;
    $redis_persist = false;
    $redis_host = null;
    $redis_port = null;
    $redis_auth = null;

    // Should we use a Sentinel to contact a Redis master server?
    if (isset($config->sentinel)) {
        $sentinel = new RedisSentinel(parseHosts($config->sentinel->hosts), $config->sentinel->master_name);
        $address = $sentinel->getRedisMasterAddress();

        $redis_host = $address['ip'];
        $redis_port = $address['port'];
        if (isset($config->sentinel->lifetime)) $redis_lifetime = $config->sentinel->lifetime;
        if (isset($config->sentinel->auth)) $redis_auth = $config->sentinel->auth;
        if (isset($config->sentinel->persistent)) $redis_persist = $config->sentinel->persistent;
    } else {
        // No.
        $redis_lifetime = $config->redis->lifetime;
        $redis_host = $config->redis->host;
        $redis_port = $config->redis->port;
        $redis_auth = $config->redis->auth;
        $redis_persist = $config->redis->persistent;
    }

    // Setup the connection options
    $cache = new Redis(
        new FrontData([
            "lifetime" => $redis_lifetime
        ]),
        [
            "host" => $redis_host,
            "port" => $redis_port,
            "auth" => $redis_auth,
            "persistent" => $redis_persist,
            "index" => 0,
        ]
    );

    return $cache;
});

// Setup and inject a Session service
$di->set('session', function () {
    $config = $this->get('config');
    $redis_lifetime = 3600;
    $redis_persist = false;
    $redis_host = null;
    $redis_port = null;
    $redis_auth = null;
    $redis_prefix = null;

    // Should we use a Sentinel to contact a Redis master server?
    if (isset($config->sentinel)) {
        $sentinel = new RedisSentinel(parseHosts($config->sentinel->hosts), $config->sentinel->master_name);
        $address = $sentinel->getRedisMasterAddress();

        $redis_host = $address['ip'];
        $redis_port = $address['port'];
        if (isset($config->sentinel->lifetime)) $redis_lifetime = $config->sentinel->lifetime;
        if (isset($config->sentinel->auth)) $redis_auth = $config->sentinel->auth;
        if (isset($config->sentinel->persistent)) $redis_persist = $config->sentinel->persistent;
        if (isset($config->sentinel->key_prefix)) $redis_prefix = $config->sentinel->key_prefix;
    } else {
        // No.
        $redis_lifetime = $config->redis->lifetime;
        $redis_host = $config->redis->host;
        $redis_port = $config->redis->port;
        $redis_auth = $config->redis->auth;
        $redis_persist = $config->redis->persistent;
        if (isset($config->redis->key_prefix)) $redis_prefix = $config->redis->key_prefix;
    }

    $session = new Phalcon\Session\Adapter\Redis([
        'host' => $redis_host,
        'port' => $redis_port,
        'auth' => $redis_auth,
        'persistent' => $redis_persist,
        'lifetime' => $redis_lifetime,
        'prefix' => $redis_prefix,
    ]);

    $session->start();
    return $session;
}, true);

// Setup and inject a FlashSession service
$di->set('flashSession', function () {
    $flash = new \Phalcon\Flash\Session([
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice' => 'alert alert-info',
        'warning' => 'alert alert-warning',
    ]);

    $flash->setAutoescape(false);

    return $flash;
});

// Setup and inject a Flash service
$di->set('flash', function () {
    $flash = new \Phalcon\Flash\Direct([
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice' => 'alert alert-info',
        'warning' => 'alert alert-warning',

    ]);

    $flash->setAutoescape(false);


    return $flash;
});

// Inject widget factory
$di->setShared(
    "coreWidgetFactory",
    function () {
        return new Multiple\Core\Widgets\Factory\CoreWidgetFactory();
    }
);

// Inject settings factory
$di->setShared(
    "settingsFactory",
    function () {
        return new Multiple\Core\Factory\SettingsFactory();
    }
);

// Inject settingsValidator factory
$di->setShared(
    "settingsValidatorFactory",
    function () {
        return new Multiple\Core\Factory\SettingsValidatorFactory();
    }
);

// Inject settings factory
$di->setShared(
    "securityUtils",
    function () {
        return new Multiple\Core\Utils\SecurityUtils();
    }
);

// Inject settings factory
$di->setShared(
    "translationUtils",
    function () {
        return new Multiple\Core\Utils\TranslationUtils();
    }
);

// Inject translator component
$di->setShared(
    "translator",
    function () {
        $localeComponent = new Multiple\Locale\LocaleLoader();
        return $localeComponent->getLocaleTranslations();
    }
);

$di->setShared(
    "frontendTranslator",
    function () {
        $localeComponent = new Multiple\Locale\LocaleLoader();
        return $localeComponent;
    }
);

$di->setShared(
    "constants",
    function () {
        return new \Multiple\Core\Utils\ConstantsUtils();
    }
);