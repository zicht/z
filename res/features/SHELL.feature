Feature: Custom shell
In order to use a different interpreter
As a user
I need to be able to set the SHELL to another interpreter than the default

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    SHELL: "/usr/bin/python"

    tasks:
        hello: |
            import sys;
            sys.stdout.write("Hello world!\n")
            sys.stdout.write("5 times 5 equals %d\n" % (5 * 5))
    """

  Scenario: Running hello
    When I run "z hello"
    Then I should see text matching "/Hello world!\n/"
    And I should see text matching "/5 times 5 equals 25\n/"


