### Grafana

#### Dostęp do Grafany
Po uruchomieniu kontenerów z aplikacją ([patrz tutaj](https://kkedzierski.github.io/chess-tournaments/pl/technicalGuide/running/)) grafana, loki oraz promtail są automatycznie uruchamiane. 
- Grafana dostępna jest pod adresem `http://localhost:3500`.
- Domyślne dane logowania to `admin` oraz `password`.

#### Konfiguracja Grafany
	1.	Przejdź do http://localhost:3500.
	2.	Zaloguj się do Grafany.
	3.	Przejdź do “Configuration” -> “Data Sources” -> “Add data source”.
	4.	Wybierz “Loki” i ustaw URL na http://chess-tournament-loki:3100.
	5.	Kliknij “Save & Test”.