#!/usr/bin/env bash

branch_name=$(git symbolic-ref --short -q HEAD)

echo "Pushing changes to the repository"
echo "Current branch: $branch_name";
if [ ${branch_name} == 'main' ]; then
    git add documentation/
    git commit -m "#main Documentation update"
    git push origin main
else
  echo "skipping documentation update for branch $branch_name";
fi


#echo "Building documentation"
#mkdocs build -f documentation/config/pl/mkdocs.yml
#mkdocs build -f documentation/config/en/mkdocs.yml
#
#echo "Deploying documentation"
#mkdocs gh-deploy -f documentation/config/pl/mkdocs.yml
#mkdocs gh-deploy -f documentation/config/en/mkdocs.yml
