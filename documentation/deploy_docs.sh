#!/usr/bin/env bash

echo "Building documentation"
mkdocs build -f documentation/config/pl/mkdocs.yml
mkdocs build -f documentation/config/en/mkdocs.yml

echo "Deploying documentation"
mkdocs gh-deploy -f documentation/config/pl/mkdocs.yml
mkdocs gh-deploy -f documentation/config/en/mkdocs.yml
