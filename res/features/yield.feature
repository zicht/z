Feature: Yielding a value
  In order to be able to run a task atomically
  As a user
  I need to be able to define a yielded value

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    tasks:
        t:
            yield: '"foo"'
            do: echo "bar"

        b: echo $(tasks.t) $(tasks.t)
    """

  Scenario: The task is executed only once
    When I run "z b"
    Then I should see text matching "/foo foo/"
    Then I should see text matching "/bar\nfoo foo/"
    Then I should not see text matching "/bar\nbar/"
