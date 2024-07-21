
### Documentation Description
The documentation is written using Markdown language.
The website is generated using the mkdocs tool.
#### Directory Structure:
```
- assets: files shared across multiple documents
- docs: documentation files
  - pl: documentation files in Polish
  - en: documentation files in English
- site: website files, generated automatically.
- mkdocs.yml: configuration file for the website generating tool
```

### Building and Releasing Documentation on GitHub Pages
Execute the bash command
```sh
bash documentation/deploy_docs.sh
```

After executing the command:
> For the **main** branch: the documentation will be built and released to the server.

> For other branches: the documentation will be built only.


### Building and Running Documentation Locally
To build and run the documentation locally, execute the following command:
```sh
bash documentation/deploy_docs.sh -l true
```
