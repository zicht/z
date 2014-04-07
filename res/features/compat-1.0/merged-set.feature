Feature: Merged set compatibility
  In order to keep compatibility with 1.0
  As a user
  I need to be able to use merge the 'set' section of a task

  Background:
    Given I am in a test directory
    And there is file "foo/z.yml"
    """
    tasks:
      t:
        args:
          a: '"a"'
    """

  Scenario: Both variables must be set when using 'set'
    Given there is file "z.yml"
    """
    imports: ["foo/z.yml"]

    tasks:
      t:
        set:
          b: '"b"'
        do: echo $(a) $(b)
    """
    When I run "z t"
    Then I should see text matching "/a b/"

  Scenario: Both variables must be set when using 'args'
    Given there is file "z.yml"
    """
    imports: ["foo/z.yml"]

    tasks:
      t:
        args:
          b: '"b"'
        do: echo $(a) $(b)
    """
    When I run "z t"
    Then I should see text matching "/a b/"

  Scenario: Both variables must be set when importing as a plugin
    Given there is file "z.yml"
    """
    plugins: ["foo"]

    tasks:
      t:
        set:
          b: '"b"'
        do: echo $(a) $(b)
    """
    When I run "z t"
    Then I should see text matching "/a b/"


  Scenario: Both variables must be set when importing as a plugin
    Given there is file "z.yml"
    """
    plugins: ["foo"]

    tasks:
      t:
        args:
          b: '"b"'
        do: echo $(a) $(b)
    """
    When I run "z t"
    Then I should see text matching "/a b/"
