Feature: Declarations and function difference
In order to be able to lazily resolve values
As a user
I need to be able to define declarations that resolve only once

  Background:
    Given I am in a test directory
    And there is file "z.yml"
        """
        # @version ">=1.0"

        plugins:
          - declaration

        tasks:
            fn:
                - echo "$(resolve_me_always())"
                - echo "$(resolve_me_always())"
                - echo "$(resolve_me_always())"
            decl:
                - echo "$(resolve_me_once)"
                - echo "$(resolve_me_once)"
                - echo "$(resolve_me_once)"
            nsdecl:
                - echo "$(namespaced.resolve_me_once)"
                - echo "$(namespaced.resolve_me_once)"
                - echo "$(namespaced.resolve_me_once)"
            decl_error:
                - echo "$(resolve_me_once())"
        """
    And there is a file "declaration/Plugin.php"
        """
        <?php
        namespace Zicht\Tool\Plugin\Declaration;

        use Zicht\Tool\Container\Container;
        use Zicht\Tool\Plugin as BasePlugin;

        class Plugin extends BasePlugin
        {
            function setContainer(Container $container) {
                $container->decl('resolve_me_once', function() {
                    static $i = 0;
                    return (string) $i ++;
                });
                $container->fn('resolve_me_always', function() {
                    static $i = 0;
                    return (string) $i ++;
                });
                $container->decl(array('namespaced', 'resolve_me_once'), function() {
                    static $i = 0;
                    return (string) $i ++;
                });
            }
        }

        """

  Scenario: The function resolves every time
    When I run "z fn"
    Then I should see text matching "/0\n1/"

  Scenario: The declaration resolves only once
    When I run "z decl"
    Then I should see text matching "/0\n0/"

  Scenario: The declaration resolves only once
    When I run "z nsdecl"
    Then I should see text matching "/0\n0/"
