#!/usr/bin/env bash

set -e

if [[ "false" != "$TRAVIS_PULL_REQUEST" ]]; then
	echo "Not deploying pull requests."
	exit
fi


if ! [[ "master" == "$TRAVIS_BRANCH" || "$TRAVIS_BRANCH" =~ ^v?[0-9](\.[0-9])* ]]; then
	echo "Not on the 'master' branch or tags."
	exit
fi

rm -rf .git
rm -r .gitignore

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


if [[ "master" == "$TRAVIS_BRANCH" ]]; then

	echo "deploy as 'latest'"

	git commit --quiet -m "Deploy from travis"
	git push --force --quiet "https://${GH_TOKEN}@${GH_REF}" master:latest > /dev/null 2>&1
fi

if [[ "$TRAVIS_BRANCH" =~ ^v?[0-9](\.[0-9])* ]]; then

	TAG_NAME=${TRAVIS_BRANCH}-release
	echo "deploy as tagged '$TAG_NAME'"

	git commit --quiet -m "Deploy from travis"
	git tag $TAG_NAME
	git push --force --quiet "https://${GH_TOKEN}@${GH_REF}" --tags > /dev/null 2>&1
fi
