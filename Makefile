# This file contains shortcuts for developing wp-smart-honeypot.
# There is no magic here, it just combines commonly used items into
# single make targets.

.PHONY: docs test

# Constants.


# Generate PHP Docs
docs:
	rm -rf docs
	phpdoc -d . -t docs

# Run all tests
test:
	(cd tests && phpunit --verbose .)

# Run the code validation (Code Sniffer)
verify:
	phpcs --standard=WordPress wp-smart-honeypot.php 