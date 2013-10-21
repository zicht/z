Feature: Concatenation compatibility
In order to keep compatibility with 1.0
As a user
I need to be able to use the concatenation operator

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    tasks:
        t:
            do: echo $("left" . "right")
    """

  Scenario: The concatenation must work as expected
    When I run "z t"
    Then I should see text matching "/leftright/"

  Scenario: The deprecation warning must be emitted
    When I run "z t"
    Then I should see text matching "/[DEPRECATED].*concatenation.*/"

