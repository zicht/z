Feature: Making an executable script
  In order to use a ZFile as a script
  As a user
  I need to be able to make the file executable and run it stand-alone

  Scenario:
    Given I am in a test directory
    And there is an executable file "z.yml" with a shebang pointing to Z
    """
    # @version ">=1.0"

    tasks:
      foo: echo "bar!"
    """
    When I run "./z.yml foo"
    Then I should see text matching "/bar!\n/"

  Scenario:
    Given I am in a test directory
    And there is an executable file "foo" with a shebang pointing to Z
    """
    # @version ">=1.0"

    tasks:
      foo: echo "bar!"
    """
    When I run "./foo foo"
    Then I should see text matching "/bar!\n/"