Z is a build and deployment management tool, originally designed for PHP-based projects.

It utilizes a simple declarative DSL, based on YML, to define tasks and configuration. The configuration can be used (and validated) by plugins, that provide their own set of tasks and / or language extensions.

Task definition

tasks:
	namespace.taskname:
		set:
			foo: "value"			# A string value variable that is injected into the execution scope
			bar: ? "default-value" 	# A string value that is overridable and defaults to "default-value"
			baz: ? 					# A variable that is required by the task
		unless: expression # if the expression evaluates to true, the task´s body and triggers are skipped. Prerequisites are called, though.
		pre: 	# prerequisites
			- @another.task
			- Some shell script make use of variable $(foo)
			- ?(condition) Some conditional shell script
		do:     # task command
			- @another.task
			- Some shell script with a $(variable)
			- ?(condition) Some conditional shell script
		post:	# task triggers
			- @another.task
			- Some shell script
			- ?(condition) Some conditional shell script

Tasks are defined by prerequisites, a body identified by the "do" section and task triggers, which trigger commands and/or other tasks when the task is executed.

