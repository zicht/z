Feature: Custom shell
In order to use a different interpreter
As a user
I need to be able to set the SHELL to another interpreter than the default

  Background:
    Given I am in a test directory

  Scenario: Using a Python shell
    Given there is file "z.yml"
    """
    # @version ">=1.1"

    SHELL: "/usr/bin/python"

    tasks:
        hello: |
            import sys;
            sys.stdout.write("Hello world!\n")
            sys.stdout.write("5 times 5 equals %d\n" % (5 * 5))
    """
    When I run "z hello"
    Then I should see text matching "/Hello world!\n/"
    And I should see text matching "/5 times 5 equals 25\n/"

  Scenario: Using a PHP shell
    Given there is file "z.yml"
    """
    # @version ">=1.1"

    SHELL: "/usr/bin/env php"

    tasks:
        hello: |
            <?php echo 10 * 10, "\n", $(20 * 10), "\n";
    """
    When I run "z hello"
    Then I should see text matching "/100\n200/"


