Feature: Using standard functions
  In order to be able to write cool scripts
  As a user
  I need to be able to use several functions out of the box

  Background:
    Given I am in a test directory

  Scenario: mtime()
    Given there is a file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t: echo $(mtime("z.yml"))
    """
    When I run "z t"
    Then I should see text matching "/\d{6,}/"

  Scenario: ctime()
    Given there is a file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t: echo $(ctime("z.yml"))
    """
    When I run "z t"
    Then I should see text matching "/\d{6,}/"

  Scenario: is_file()
    Given there is a file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t:
          - ?(is_file("z.yml")) echo "yup"
          - ?(is_file("foo.txt")) echo "nope"
    """
    When I run "z t"
    Then I should see text matching "/yup/"
    And I should not see text matching "/nope/"

  Scenario: cat
    Given there is a file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t: echo $(cat("a", "b", "c"))
    """
    When I run "z t"
    Then I should see text matching "/abc/"

  Scenario: sprintf
    Given there is a file "z.yml"
    """
    # @version ">=1.0"

    tasks:
        t: echo $(sprintf("a%sc", "b"))
    """
    When I run "z t"
    Then I should see text matching "/abc/"

