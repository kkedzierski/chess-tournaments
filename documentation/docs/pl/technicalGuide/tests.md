### Testy jednostkowe

Testy są napisane w PHP i wykorzystują framework PHPUnit.
Aby uruchomić testy jednostkowe.
Wykonaj polecenie w głównym katalogu projektu:

```sh
bash bin/unit.sh
```

### Testy mutacyjne
Testy są napisane w PHP i wykorzystują (Infection)[https://infection.github.io/guide/].
Aby uruchomić testy mutacyjne.
Wykonaj polecenie w głównym katalogu projektu:

```sh
bash bin/infection.sh
```

### Wszystkie testy

Aby uruchomić wszystkie testy
Wykonaj polecenie w głównym katalogu projektu:

```sh
bash bin/test.sh
```

### Konfiguracja
Testy mutacyjne są konfigurowane w pliku `.infection.json5`.