
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

### Zbudowanie i wypuszczenie dokumentacji na gihub pages
Wykonaj polecenie bash 
```sh
bash documentation/deploy_docs.sh
```

Po wykonaniu polecenia:
> Dla brancha **main**:  dokumentacja zostanie zbudowana i wypuszczona na serwer.

> Dla innych branchów: dokumentacja zostanie tylko zbudowana.


### Zbudowanie i uruchomienie dokumentacji lokalnie
Aby zbudować i uruchomić dokumenatcję lokalnie, wykonaj poniższe polecenie:
```sh
bash documentation/deploy_docs.sh -l true
```
