Feature: Env compatibility
  In order to keep compatibility with 1.0
  As a user
  I need to be able to use the env settings as before

  Background:
    Given I am in a test directory
    And there is file "z.yml"
    """
    env:
        env_name:
            ssh: 'homer@springfield'
    tasks:
        t:
            set:
                env: ?
            do: echo $(env.ssh) $(env)
    """

  Scenario: The env properties must work as expected
    When I run "z t env_name"
    Then I should see text matching "/homer@springfield/"

  Scenario: The env name must work as expected
    When I run "z t env_name"
    Then I should see text matching "/\benv_name\b/"

  Scenario: The deprecation warning about env config must be emitted
    When I run "z t env_name"
    Then I should see text matching "/\[DEPRECATED\][^\n]*env[^\n]*must be replaced by[^\n]*envs/"

  Scenario: The deprecation warning about env input variable must be emitted
    When I run "z t a"
    Then I should see text matching "/\[DEPRECATED\][^\n]*env[^\n]*target_env/"
