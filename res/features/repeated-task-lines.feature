Feature: Using repeated task lines
  In order to be able to loop over lists of items
  As a user
  I need to be able to use expressions to iterate lines


Background:
  Given I am in a test directory
  Given there is a file "z.yml"
  """
  # @version ">=2.0"

  globals:
      a: ['a', 'b', 'c']
      b: []

  tasks:
      t:  @(each globals.a) echo "$(_key) => $(_value)"
      t2: @(each globals.b) echo "NEVER!"

  """

  Scenario:
    When I run "z t"
    Then I should see text matching "/0 => a/"
    And I should see text matching "/1 => b/"
    And I should see text matching "/2 => c/"

  Scenario:
    When I run "z t2"
    Then I should not see text matching "/NEVER/"
