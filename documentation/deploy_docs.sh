#!/usr/bin/env bash

while getopts l: flag
do
    case "${flag}" in
        l) local=${OPTARG};;
    esac
done

echo "$local";
if [ "$local" == "true" ]; then
  echo "Serving documentation locally..."
  mkdocs serve -f documentation/config/pl/mkdocs.yml
else
    branch_name=$(git symbolic-ref --short -q HEAD)

    echo "Pushing changes to the repository..."
    echo "Current branch: $branch_name";
    if [ ${branch_name} == 'main' ]; then
        git add documentation/
        git commit -m "#main Documentation update"
        git push origin main
    else
      echo "Skipping pushing documentation to repository for branch $branch_name.";
    fi

    echo "Building documentation..."
    mkdocs build -f documentation/config/pl/mkdocs.yml
    mkdocs build -f documentation/config/en/mkdocs.yml

    echo "Deploying documentation..."
    mkdocs gh-deploy -f documentation/config/pl/mkdocs.yml
    mkdocs gh-deploy -f documentation/config/en/mkdocs.yml
fi
