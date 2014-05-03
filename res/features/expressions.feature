Feature: Expressions
  In order to do compile-time evaluation
  As a user
  I need to be able to define common expressions

  Background:
    Given I am in a test directory
    And there is file "z.yml"
        """
        tasks:
            add:        echo $(1 + 2)
            subtract:   echo $(3 - 2)
            multiply:   echo $(3 * 7)
            divide:     echo $(24 / 3)
            negative:   echo $(1 + -2)

            precede_mul1:   echo $(4 + 3 * 3)
            precede_mul2:   echo $(3 * 3 + 5)
            precede_paren:  echo $(3 * (3 + 5))
            precede_paren2:  echo $(3 * -(2 + 5))

            bool:
              args:
                something: ? false
              unless: !something
              do: echo "anyway"
        """

  Scenario: Addition
    When I run "z add"
    Then I should see text matching "/3\n/"

  Scenario: Subtraction
    When I run "z subtract"
    Then I should see text matching "/1\n/"

  Scenario: Multiplication
    When I run "z multiply"
    Then I should see text matching "/21\n/"

  Scenario: Division
    When I run "z divide"
    Then I should see text matching "/8\n/"

  Scenario: Negative
    When I run "z negative"
    Then I should see text matching "/-1\n/"

  Scenario: Precedence
    When I run "z precede-mul1"
    Then I should see text matching "/13\n/"

  Scenario: Precedence
    When I run "z precede-mul2"
    Then I should see text matching "/14\n/"

  Scenario: Precedence
    When I run "z precede-paren"
    Then I should see text matching "/24\n/"

  Scenario: Precedence
    When I run "z precede-paren2"
    Then I should see text matching "/-21\n/"

  Scenario: Precedence
    When I run "z bool"
    Then I should not see text matching "/anyway\n/"

  Scenario: Precedence
    When I run "z bool 1"
    Then I should see text matching "/anyway\n/"

