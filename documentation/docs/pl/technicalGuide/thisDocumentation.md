
### Opis dokumentacji
Dokumentacja pisana jest za pomocą języka markdown.
Strona internetowa generowana jest za pomocą narzędzia mkdocs.
#### Struktura katalogów:
```
- assets: pliki dzielone przez wiele dokumentów
- docs: pliki dokumentacji
  - pl: pliki dokumentacji w języku polskim
  - en: pliki dokumentacji w języku angielskim
- site: pliki strony internetowej, generowane automatycznie.
- mkdocs.yml: plik konfiguracyjny dla narzędzia generującego stronę internetową
```

### Zbudowanie strony internetowej
>Automatyczne wypuszowanie dokumentacji odbywa się wyłącznie na main branchu.
> Po wykonaniu poniższego polecenia, strona internetowa zostanie zbudowana i wypuszczona na serwerze, jeżeli zmiany zostały wprowadzone na main branchu.
> W przeciwnym wypadku strona internetowa zostanie zbudowana lokalnie.

Wykonaj polecenie bash 
```sh
bash documentation/deploy_docs.sh
```


### Zbudowanie strony internetowej lokalnie

Aby zbudować stronę internetową lokalnie, wykonaj poniższe polecenia:
```sh
bash documentation/deploy_docs.sh -l=true
```