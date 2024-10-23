PATH_TO_BUILD := local_git/wco_helpdesk/Build/

.PHONY: help
help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: test ## Run all tests
test: fix-code-style code-style phpstan

.PHONY: fix ## Run all tests
fix: rector fix-code-style

.PHONY: install
install:
	Build/Scripts/runTests.sh -s composerUpdate

.PHONY: install-rector
install-rector:
	Build/Scripts/runTests.sh -s composerUpdateRector

.PHONY: code-style
code-style:
	Build/Scripts/runTests.sh -s cgl -n

.PHONY: fix-code-style
fix-code-style: ## Fix PHP coding style issues
	Build/Scripts/runTests.sh -s cgl

.PHONY: phpstan
phpstan: ## Run phpstan tests
	Build/Scripts/runTests.sh -s phpstan

.PHONY: phpstan-baseline
phpstan-baseline: ## Update the phpstan baseline
	Build/Scripts/runTests.sh -s phpstanBaseline

.PHONY: rector
rector: ## Refactor code using rector
	Build/Scripts/runTests.sh -s rector
