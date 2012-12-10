<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
require_once __DIR__ . '/../vendor/autoload.php';

// The "z" tools

$options = null;
if (file_exists($file = getcwd() . '/z.yml')) {
    $options = \Symfony\Component\Yaml\Yaml::parse($file);
} else {
    // detect if we're in a working copy:
    $prev = null;
    for ($dir = getcwd(); $dir != '/'; $dir = dirname($dir)) {
        if (is_file($dir . '/z.yml')) {
            $options = \Symfony\Component\Yaml\Yaml::parse($dir . '/z.yml');
            break;
        }
    }
    if (is_null($options)) {
        $options = array(
            'versioning' => array(
                'url' => trim(shell_exec('svn info | grep "URL" | awk \'{print $2}\' | sed \'s!trunk/!!g\''))
            ),
            'options' => array()
        );
    }
}

$params = new \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag($options);
$builder = new \Symfony\Component\DependencyInjection\ContainerBuilder($params);
$loader = new \Symfony\Component\DependencyInjection\Loader\XmlFileLoader($builder, new \Symfony\Component\Config\FileLocator(
    __DIR__ . '/../src/Zicht/Tool/Resources/'
));
$loader->load('services.xml');
$builder->compile();

$app = new Zicht\Tool\Application($builder);
$app->run();