Feature: Complex variable resolution
  In order to be able to use complex variable structures
  As a user
  I need to be able to resolve variables just as I'd expect from a decent parser.

  Background:
    Given I am in a test directory
    And there is a file "unstrict/Plugin.php"
    """
    <?php
    namespace Zicht\Tool\Plugin\Unstrict;

    use \Zicht\Tool\Container\Container;
    use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
    use \Zicht\Tool\Plugin as BasePlugin;

    class Plugin extends BasePlugin
    {
        public function appendConfiguration(ArrayNodeDefinition $rootNode)
        {
            $rootNode
                ->children()->variableNode('vars')->end()->end()
            ;
        }


        public function setContainer(Container $z)
        {
            $someObject = (object)array(
                'prop' => 'Awesome',
                'child' => array(
                    'a' => 'Loving it'
                )
            );
            $z->set('someObject', $someObject);
        }
    }

    """
    And there is file "z.yml"
    """
    # @version ">=1.0"

    plugins:
      - unstrict
    vars:
        a:
            b:
                c: Ultimate
        x:
            y:
                z: b
        foo:
            bar:
                baz: c

    tasks:
        t1: echo "t1 says $(vars.a.b.c)"
        t2: echo "t2 says $(vars.a[vars.x.y.z][vars.foo.bar.baz])"
        t3:
          echo 't3 says $(
            vars.a[
              vars.x["y"]["z"]
            ][
              vars.foo["bar"]["baz"]
            ]
          )'

        t4: echo "t4 says $(someObject.prop)"
        t5: echo "t5 says $(someObject["prop"])"

        t6: echo "t6 says $(someObject["child"].a)"
        t7: echo "t7 says $(someObject["child"]["a"])"
    """

    Scenario:
        When I run "z t1"
        Then I should see text matching "/t1 says Ultimate\n/"

    Scenario:
        When I run "z t2"
        Then I should see text matching "/t2 says Ultimate\n/"

    Scenario:
        When I run "z t3"
        Then I should see text matching "/t3 says Ultimate\n/"

    Scenario:
        When I run "z t4"
        Then I should see text matching "/t4 says Awesome\n/"

    Scenario:
        When I run "z t5"
        Then I should see text matching "/t5 says Awesome\n/"

    Scenario:
        When I run "z t6"
        Then I should see text matching "/t6 says Loving it\n/"

    Scenario:
        When I run "z t7"
        Then I should see text matching "/t7 says Loving it\n/"

