Feature: Using complex task names
  In order to organize my tasks
  As a user
  I need to be able to use group tasks by using dot-separation


  Background:
    Given I am in a test directory
    Given there is a file "z.yml"
    """
    tasks:
     some_namespace.a: echo ":)"
     some_namespace.b: @some_namespace.a
     some_namespace.c: $(tasks.some_namespace.b)
    """

  Scenario: Namespace
    When I run "z some-namespace:a"
    Then I should see text matching "/:\)/"

  Scenario: Namespace abbrevation
    When I run "z s:a"
    Then I should see text matching "/:\)/"

  Scenario: Internal naming
    When I run "z s:b"
    Then I should see text matching "/:\)/"

  Scenario: Internal naming
    When I run "z s:c"
    Then I should see text matching "/:\)/"

