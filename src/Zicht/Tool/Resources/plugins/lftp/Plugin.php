<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Philip Bergman <philip@zicht.nl>
 * @copyright 2012 Philip bergman
 */
namespace Zicht\Tool\Plugin\Lftp;

use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use \Zicht\Tool\Container\Container;
use \Zicht\Tool\Plugin as BasePlugin;

/**
 * lftp plugin
 */
class Plugin extends BasePlugin
{
    public $ftpUsername = null,
	       $ftpPassword = null,
           $ftpDomain   = null;

    /**
     * Configures the lftp parameters
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
     * @return mixed|void
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('sync')
                    ->children()
                        ->scalarNode('options')->end()
                        ->scalarNode('exclude_file')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * helper function to
     * set local vars
     *
     * @param $ftpUrl
     * @throws \Exception
     */
    public function parseFtpString($ftpUrl)
    {
        if(is_null($this->ftpUsername)){
            if(preg_match('/^(?:ftp:\/\/)?([^:]+):([^@]+)@(.*)$/',$ftpUrl,$matches)){
                $this->ftpUsername = $matches[1];
                $this->ftpPassword = $matches[2];
                $this->ftpDomain   = $matches[3];
            }else{
                throw new \Exception('SSH string in wrong fromat, use [ftp://]username:password@domain.com');
            }
        }
    }

    public function parseFile($file){
        if(file_exists($file)){

            $excludeString = ' ';
            array_walk(
                array_filter(
                    explode("\n",
                        file_get_contents(
                            $file
                        )
                    )
                ),
                function($val,$key) use(&$excludeString) {
                    $excludeString .= sprintf('--exclude %s ',$val);
                }
            );

            return $excludeString;

        }else{
            throw new \Exception(sprintf('Could not find file %s',$file));
        }
    }




    public function setContainer(Container $container)
    {

	    $self = $this;


        $container->decl('sync.exludes',
            function($container) use ($self) {
                return $self->parseFile($container->resolve('sync.exclude_file'));
            }
        );

        $container->decl('ftp.username',
            function($container) use ($self) {
                $self->parseFtpString($container->resolve('env.ssh'));
                return $self->ftpUsername;
            }
        );

        $container->decl('ftp.password',
            function($container)  use ($self){
                $self->parseFtpString($container->resolve('env.ssh'));
                return $self->ftpPassword;
            }
        );

        $container->decl('ftp.domain',
            function($container) use ($self) {
                $self->parseFtpString($container->resolve('env.ssh'));
                return $self->ftpDomain;
            }
        );

    }

}
