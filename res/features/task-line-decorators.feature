Feature: Using conditional task lines
  In order to be able to wrap task lines
  As a user
  I need to be able to use decorators to wrap the task lines in whatever I want


Background:
  Given I am in a test directory
  Given there is a file "z.yml"
  """
  tasks:
    t:
      - @("mysql -N") SELECT 4 * 4;
      - @("perl") if("The quick brown fox" =~ /ox/) { print ":)" };
  """

  Scenario:
    When I run "z t"
    Then I should see text matching "/16/"
    And I should not see text matching "/SELECT/"
    And I should see text matching "/:\)/"
    And I should not see text matching "/print/"
