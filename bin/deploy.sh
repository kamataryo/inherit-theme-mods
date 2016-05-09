#!/usr/bin/env bash

set -e

if [[ "false" != "$TRAVIS_PULL_REQUEST" ]]; then
	echo "Not deploying pull requests."
	exit
fi

if [[ "master" != "$TRAVIS_BRANCH" ]]; then
	echo "Not on the 'master' branch."
	exit
fi

rm -rf .git
rm -r .gitignore

echo "bin
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
git config user.email "mugil.cephalus@gmail.com"
git add .
git commit --quiet -m "Deploy from travis"
git push --force --quiet "https://${GH_TOKEN}@${GH_REF}" master:latest > /dev/null 2>&1
