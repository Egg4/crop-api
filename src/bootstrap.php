<?php

use Egg\Container;

include_once(ROOT_DIR . '/vendor/autoload.php');

/**
 * Autoloader
 */
spl_autoload_register(function($class) {
    if (strpos($class, 'App\\') === 0) {
        $dir = strcasecmp(substr($class, -4), 'Test') ? 'src' : 'test';
        $name = substr($class, strlen('App'));
        $path = ROOT_DIR . DIRECTORY_SEPARATOR . $dir . strtr($name, '\\', DIRECTORY_SEPARATOR) . '.php';
        if (file_exists($path)) require $path;
    }
});

/**
 * Services
 */
function registerServices(Container $container)
{
    $container['config'] = function() {
        $globalConfig = parse_ini_file(sprintf('%s/src/Config/global.ini', ROOT_DIR), true);
        $envConfig = parse_ini_file(sprintf('%s/src/Config/%s.ini', ROOT_DIR, APP_ENV), true);
        $config = array_replace_recursive($globalConfig, $envConfig);
        return new \Egg\Yolk\Set($config);
    };

    $container['logger'] = function($container) {
        return new \Egg\Logger\File($container['config']['logger']);
    };

    $container['password'] = new Container([
        'hash' => function() {
            return function($password) {
                return password_hash($password, PASSWORD_DEFAULT);
            };
        },
        'verify' => function() {
            return function($password, $hash) {
                return password_verify($password, $hash);
            };
        },
    ]);

    $container['router'] = function() {
        return new \Egg\Router();
    };

    $container['cache'] = new Container([
        'file' => function() use ($container) {
            return new \Egg\Cache\File($container['config']['cache.file']);
        },
    ]);

    $container['authenticator'] = function($container) {
        return new \Egg\Authenticator\Token([
            'secret'    => $container['config']['authentication']['secret'],
            'timeout'   => $container['config']['authentication']['timeout'],
        ]);
    };

    $container['resolver'] = new Container([
        'class' => function() {
            return new \Egg\Resolver\ClassName([
                'search'    => '\App\Resource\{resource}\{class}',
                'fallback'  => '{fallback}',
            ]);
        },
        'method' => function() {

        },
    ]);

    $container['database'] = function($container) {
        return new \Egg\Orm\Database\Pdo($container['config']['database']);
    };

    $container['schema'] = function($container) {
        return new \Egg\Orm\Schema\Mysql([
            'database'  => $container['database'],
            'cache'     => $container['cache']['file'],
        ]);
    };

    $container['repository'] = new Container(function($resource) use ($container) {
        $class = $container['resolver']['class']->resolve([
            'resource'      => $resource,
            'class'         => 'Repository',
            'fallback'      => '\Egg\Orm\Repository\Generic',
        ]);
        return new $class([
            'container' => $container,
            'resource'  => $resource,
        ]);
    });

    $container['factory'] = new Container(function($resource) use ($container) {
        $class = $container['resolver']['class']->resolve([
            'resource'      => $resource,
            'class'         => 'Factory',
            'fallback'      => '\Egg\Orm\Factory\Generic',
        ]);
        return new $class([
            'container' => $container,
            'resource'  => $resource,
        ]);
    });

    $container['authorizer'] = new Container(function($resource) use ($container) {
        $class = $container['resolver']['class']->resolve([
            'resource'      => $resource,
            'class'         => 'Authorizer',
            'fallback'      => '\App\Authorizer',
        ]);
        return new $class([
            'container' => $container,
            'resource'  => $resource,
        ]);
    });

    $container['validator'] = new Container(function($resource) use ($container) {
        $class = $container['resolver']['class']->resolve([
            'resource'      => $resource,
            'class'         => 'Validator',
            'fallback'      => '\Egg\Validator\Generic',
        ]);
        return new $class([
            'container' => $container,
            'resource'  => $resource,
        ]);
    });

    $container['controller'] = new Container(function($resource) use ($container) {
        $class = $container['resolver']['class']->resolve([
            'resource'      => $resource,
            'class'         => 'Controller',
            'fallback'      => '\Egg\Controller\Generic',
        ]);
        return new $class([
            'container' => $container,
            'resource'  => $resource,
        ]);
    });

    $container['serializer'] = new Container(function($resource) use ($container) {
        $class = $container['resolver']['class']->resolve([
            'resource'      => $resource,
            'class'         => 'Serializer',
            'fallback'      => '\Egg\Serializer\Generic',
        ]);
        return new $class;
    });

    $container['parser'] = new Container([
        'application/x-www-form-urlencoded' => function() {
            return new \Egg\Parser\UrlEncoded();
        },
        'application/json' => function() {
            return new \Egg\Parser\Json();
        },
        'application/xml' => function() {
            return new \Egg\Parser\Xml();
        },
        'text/xml' => function() {
            return new \Egg\Parser\Xml();
        },
    ]);

    $container['formatter'] = new Container([
        'application/x-www-form-urlencoded' => function() {
            return new \Egg\Formatter\UrlEncoded();
        },
        'application/json' => function() {
            return new \Egg\Formatter\Json();
        },
        'application/xml' => function() {
            return new \Egg\Formatter\Xml();
        },
        'text/xml' => function() {
            return new \Egg\Formatter\Xml();
        },
    ]);
}

/**
 * Components
 */
function registerComponents(Container $container)
{
    $container['components'] = [
        new \Egg\Component\Http\Response\ContentType([
            'media.types'       => $container['config']['response']['media.types'],
            'charset'           => $container['config']['response']['charset'],
        ]),
        new \Egg\Component\Http\Exception(),
        new \Egg\Component\Http\Cors([
            'origin'            => $container['config']['cors']['origin'],
            'methods'           => $container['config']['cors']['methods'],
            'headers.allow'     => $container['config']['cors']['headers.allow'],
            'headers.expose'    => $container['config']['cors']['headers.expose'],
        ]),
        new \Egg\Component\Http\Request\Accept([
            'media.types'       => $container['config']['response']['media.types'],
        ]),
        new \Egg\Component\Http\Request\ContentEncoding([
            'media.encodings'   => $container['config']['request']['media.encodings'],
        ]),
        new \Egg\Component\Http\Request\ContentType([
            'media.types'       => $container['config']['request']['media.types'],
        ]),
        new \Egg\Component\Http\Route(),
        new \Egg\Component\Http\Authentication([
            'header.key'        => $container['config']['authentication']['header.key'],
            'routes'            => $container['config']['authentication']['routes'],
        ]),
        new \Egg\Component\Resource\Filter(),
        new \Egg\Component\Resource\Range(),
        new \Egg\Component\Resource\Sort(),
        new \Egg\Component\Resource\Resource(),
        new \Egg\Component\Resource\Action\Create(),
        new \Egg\Component\Resource\Action\Custom(),
        new \Egg\Component\Resource\Action\Delete(),
        new \Egg\Component\Resource\Action\Read(),
        new \Egg\Component\Resource\Action\Replace(),
        new \Egg\Component\Resource\Action\Search(),
        new \Egg\Component\Resource\Action\Select(),
        new \Egg\Component\Resource\Action\Update(),
    ];
}