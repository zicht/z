Feature: Making an executable script
  In order to use a ZFile as a script
  As a user
  I need to be able to make the file executable and run it stand-alone

  Scenario:
    Given I am in a test directory
    And there is an executable file "foo" with a shebang pointing to Z
    """
    tasks:
      foo: echo "bar!"
    """
    When I run "./foo foo"
    Then I should see text matching "/bar!\n/"