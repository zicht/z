Feature: Packager
In order to be able to distribute packaged builds
As a user
I need to be able to build a phar package from my zfile.

  Background:
    Given I am in a test directory
    And there is file "z.yml"
        """
        tasks:
            t: echo "Hello world from package!"
        """
    And I run "package build a.out --static=z.yml --verbose --force"

  Scenario: The packager reports the written file
    Then I should see text matching "/Built [^\n]+a.out in \d+(\.\d+)? seconds/"

  Scenario: The value in the task resolves to the declaration of the plugin
    When I run "./a.out t"
    Then I should see text matching "/Hello world/m"
