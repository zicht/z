Feature: Multivalued arguments
  In order to support multiple values as arguments
  As a user
  I need to be able to provide arrays as default values

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    globals:
        a:
          - 1
          - 2
          - 3

    tasks:
        t:
            args:
                bar[]: ? globals.a
            do:
                - @(each bar) echo "$(_key) => $(_value)"
    """

  Scenario:
    When I run "z t"
    Then I should see text matching "/0 => 1\n1 => 2\n2 => 3/"


