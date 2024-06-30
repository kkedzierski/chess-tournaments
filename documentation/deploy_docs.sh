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
  source myenv/bin/activate
  mkdocs serve -f documentation/config/mkdocs.yml
    if [ $? -ne 0 ]; then
      echo "Failed to serve documentation locally."
      echo "Try install python virtual env by running 'python3 -m venv myenv' and try again."
      exit 1
    fi
else
    echo "Building documentation..."
    source myenv/bin/activate
    mkdocs build -f documentation/config/mkdocs.yml
    if [ $? -ne 0 ]; then
      echo "Failed to build documentation."
      echo "Try install python virtual env by running 'python3 -m venv myenv' and try again."
      exit 1
    fi

    echo "Deploying documentation..."
    mkdocs gh-deploy -f documentation/config/mkdocs.yml

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
fi
