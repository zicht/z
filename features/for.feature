Feature: Using repeated task lines
  In order to be able to loop over lists of items
  As a user
  I need to be able to use expressions to iterate lines


Background:
  Given I am in a test directory

  Scenario: Looping over an array
    Given there is a file "z.yml"
    """
    # @version ">=2.0"

    globals:
        a:
          - a
          - b
          - c

    tasks:
        t:  @(for i, v in globals.a) echo "$(i) => $(v)"
    """
    When I run "z t"
    Then I should see text matching "/0 => a/"
    And I should see text matching "/1 => b/"
    And I should see text matching "/2 => c/"

  Scenario:
    Given there is a file "z.yml"
    """
    # @version ">=2.0"

    globals:
        a:
          - a
          - b
          - c

    tasks:
        t:  @(each globals.a) echo "foo"
    """
    When I run "z t"
    Then I should see text matching "/foo\nfoo\nfoo/"

  Scenario:
    Given there is a file "z.yml"
    """
    # @version ">=2.0"

    globals:
        a:
          - a
          - b
          - c

    tasks:
        t:  @(for v in globals.a) echo "$(v)"
    """
    When I run "z t"
    Then I should see text matching "/a\nb\nc/"

  Scenario:
    Given there is a file "z.yml"
    """
    # @version ">=2.0"

    globals:
        a:

    tasks:
        t:  @(for i, v in globals.a) echo "NEVER"
    """
    When I run "z t"
    Then I should not see text matching "/NEVER/"