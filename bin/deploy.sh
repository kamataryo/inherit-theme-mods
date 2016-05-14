#!/usr/bin/env bash

set -e

if ! [[ "$WP_VERSION"         == "$WP_VERSION_TO_DEPLOY" && \
	    "$TRAVIS_PHP_VERSION" == "$PHP_VERSION_TO_DEPLOY" && \
	    "$WP_MULTISITE"       == "$WP_MULTISITE_TO_DEPLOY" ]]; then
	echo "Not deploying from this matrix";
	exit
elif [[ "false" != "$TRAVIS_PULL_REQUEST" ]]; then
	echo "Not deploying pull requests."
	exit
elif ! [[ "master" == "$TRAVIS_BRANCH" ]]; then
	echo "Not on the 'master' branch."
	if [[ "" == "$TRAVIS_TAG" ]]; then
		echo "Not tagged."
		exit
	else
		echo "tagged."
	fi
fi

COMMIT_MESSAGE=$(git log --format=%B -n 1 "$TRAVIS_COMMIT")

rm -rf .git
echo "README.md
bin
.travis.yml
.editorconfig
.gitignore
assets/*.coffee
tests
phpunit.xml.dist
package.json
node_modules
gulpfile.js
Gruntfile.js
bower.json
.bowerrc" > .gitignore

git init
git config user.name "kamataryo"
git config user.email "kamataryo@travis-ci.org"
git add .
git commit --quiet -m"Deploy from travis." -m"Original commit is $TRAVIS_COMMIT."

if [[ "master" == "$TRAVIS_BRANCH" ]]; then
	echo "deploy on 'latest' branch, tested on PHP=$TRAVIS_PHP_VERSION & WP=$WP_VERSION"
	option="master:latest"
fi
if ! [[  "" == "$TRAVIS_TAG" ]]; then
	echo "deploy as '$TRAVIS_TAG', tested on PHP=$TRAVIS_PHP_VERSION & WP=$WP_VERSION"
	git push --force --quiet "https://${GH_TOKEN}@${GH_REF}" ":$TRAVIS_TAG" > /dev/null 2>&1
	git tag "$TRAVIS_TAG" -m"$COMMIT_MESSAGE" -m"Original commit is $TRAVIS_COMMIT."
	option="--tags"
fi

git push --force --quiet "https://${GH_TOKEN}@${GH_REF}" "$option" > /dev/null 2>&1

exit 0
