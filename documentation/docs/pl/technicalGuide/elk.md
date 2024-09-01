### ELK
Elasticsearch, Logstash oraz Kibana to trzy narzędzia, 
które razem tworzą Elastic Stack.
Służą one do zbierania, przetwarzania, wizualizacji oraz analizy danych.
W naszym przypadku wykorzystujemy je do zbierania oraz wizualizacji logów aplikacji.

- Wszystkie logi aplikacji są zbierane w Elasticsearch.
- Logi aplikacji są przetwarzane przez Logstash.
- Kibana służy do wizualizacji logów aplikacji.

#### Dostęp do ELK
Po uruchomieniu kontenerów z aplikacją ([patrz tutaj](https://kkedzierski.github.io/chess-tournaments/pl/technicalGuide/running/)) Elasticsearch, Logstash oraz Kibana zostaną automatycznie uruchomione. 
- Kibana dostępna jest pod adresem `http://localhost:36505`.
- Elasticsearch dostępny jest pod adresem `http://localhost:9200`.

#### Konfiguracja Kibany / Tworzenie indeksu
	1.  Przejdź do http://localhost:36505.
	2.  Przejdź do sekcji “Discover” dostępnej w lewym bocznym menu.
	3.  Wpisz w polu index pattern frazę "chess-tournaments-logs-*" i kliknij “Next step”.
	4.  Wybierz pole "@timestamp" jako czas wydarzenia i kliknij “Create index pattern”.
    5.  W sekcji Discrover możesz przeglądać logi aplikacji.

#### Uruchamianie security
- Utwórz plik `.env` w folderze `docker` na podstawie pliku `.env.dist`.
- Ustaw wartości zmiennych środowiskowych w pliku `.env`:

```angular2html
ELASTICSEARCH_USERNAME=TwojaNazwaUżytkownika
ELASTICSEARCH_PASSWORD=TwojeHasło
ELASTICSEARCH_SECURITY="true"
```

#### Zmiana nazwy indeksu
- W konfiguracji logstash `docker/etc/logstash/conf.d/default.conf` zmień wartość w obiekcie output dla wartości index
```angular2html
output {
  elasticsearch {
    hosts => ["http://chess-tournaments-elasticsearch:9200"]
    index => Tutaj
  }
}
```

#### Dodanie nowego pola do logów
- W konfiguracji logstash `docker/etc/logstash/conf.d/default.conf` dodaj nowy file do obiektu input
```angular2html
  file {
    type => "test"
    path => "/var/www/chess-tournaments/log/test.log"
    start_position => "beginning"
  }
```


### Ważne
- Niezbędne jest aby w pliku docker-compose.yaml port dla elasticsearcha pozostały niezmienne "9200:9200"
- Zmiana nazwy kontenera chess-tournaments-elasticsearch powinna być również wykonana w `docker/etc/logstash/conf.d/default.conf`
