Feature: Plugin definition
In order to be able to extend Z
As a user
I need to be able to add tasks, functions and declarations in a Plugin

  Background:
    Given I am in a test directory
    And there is file "z.yml"
        """
        plugins: ["myplugin"]

        tasks:
            t: echo "$(hello)"
            t2: echo "$(hello2())"
            t3:
                pre: echo "Pre from z.yml"
                do: echo "$(hello2())"
                post: echo "Post from z.yml"
        """
    And there is a file "myplugin/z.yml"
        """
        tasks:
            t3:
                pre: echo "Pre from plugin!"
                do: echo "Do from plugin"
                post: echo "Post from plugin!"
        """
    And there is a file "myplugin/Plugin.php"
        """
        <?php
        namespace Zicht\Tool\Plugin\Myplugin;

        use Zicht\Tool\Container\Container;
        use Zicht\Tool\Plugin as BasePlugin;

        class Plugin extends BasePlugin
        {
            function setContainer(Container $container) {
                $container->decl('hello', function() {
                    return 'Foo bar!';
                });
                $container->fn('hello2', function() {
                    return 'Foo baz!';
                });
            }
        }

        """

  Scenario: The value in the task resolves to the declaration of the plugin
    When I run "z t"
    Then I should see text matching "/Foo bar!/"

  Scenario: The value in the task resolves to the function of the plugin
    When I run "z t2"
    Then I should see text matching "/Foo baz!/"

  Scenario: The pre and post from the plugged in task are executed, but the do is overridden.
    When I run "z t3"
    Then I should see text matching "/Foo baz!/"
    Then I should see text matching "/Pre from plugin!.*Pre from z.yml/s"
    Then I should see text matching "/Post from plugin!.*Post from z.yml/s"
    But I should not see text matching "/Do from plugin/s"

