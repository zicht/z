Feature: Yielding a value
  In order to be able to run a task atomically
  As a user
  I need to be able to define a yielded value

  Background:
    Given I am in a test directory

  Scenario: The task is executed only once
    And there is file "z.yml"
    """
    tasks:
        t:
            yield: '"foo"'
            do: echo "bar"

        b: echo $(tasks.t) $(tasks.t)
    """
    When I run "z b"
    Then I should see text matching "/foo foo/"
    Then I should see text matching "/bar\nfoo foo/"
    Then I should not see text matching "/bar\nbar/"

  Scenario: The task is executed only once, even when using it as a var
    And there is file "z.yml"
    """
    tasks:
        t:
            yield: '"foo"'
            do: echo "bar"

        b:
          set:
            var: tasks.t
          do: echo $(tasks.t) $(tasks.t)
    """
    When I run "z b"
    Then I should see text matching "/foo foo/"
    Then I should see text matching "/bar\nfoo foo/"
    Then I should not see text matching "/bar\nbar/"


  Scenario: The task is executed only once, even when used by a dependent task
    And there is file "z.yml"
    """
    tasks:
        t:
            yield: '"foo"'
            do: echo "bar"

        a:
          set:
            var: tasks.t

        b:
          set:
            var: tasks.t
          pre:
            - @a
          do: echo $(tasks.t) $(tasks.t)
    """
    When I run "z b"
    Then I should see text matching "/foo foo/"
    Then I should see text matching "/bar\nfoo foo/"
    Then I should not see text matching "/bar\nbar/"
