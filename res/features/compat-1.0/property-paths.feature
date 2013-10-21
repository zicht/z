Feature: Task definition
  In order to keep compatibility with 1.0
  As a user
  I need to be able to use property paths as before

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    plugins: ['myplugin']
    tasks:
        t:
            do: echo $(fn)
    """
    And there is a file "myplugin/Plugin.php"
    """
    <?php
    namespace Zicht\Tool\Plugin\Myplugin;

    use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
    use \Zicht\Tool\Container\Container;
    use \Zicht\Tool\Plugin as BasePlugin;

    class Plugin extends BasePlugin
    {
        public function appendConfiguration(ArrayNodeDefinition $rootNode)
        {
            $rootNode
                ->children()
                    ->arrayNode('a')
                        ->children()
                            ->scalarNode('b')
                                ->defaultValue("value of a.b")
                            ->end()
                        ->end()
                        ->addDefaultsIfNotSet()
                    ->end()
                ->end();
        }

        function setContainer(Container $c)
        {
            $c->decl('fn', function($c) {
                return $c->resolve('a.b');
            });
        }
    }
    """

  Scenario: The string path must work as expected
    When I run "z t"
    Then I should see text matching "/value of a\.b/"

  Scenario: The deprecation warning must be emitted
    When I run "z t"
    Then I should see text matching "/\[DEPRECATED\][^\n]*Resolving variables by strings[^\n]*use arrays/"
