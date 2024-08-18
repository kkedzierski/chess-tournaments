### Grafana

#### Access to Grafana
After starting the containers with the application ([see here](https://kkedzierski.github.io/chess-tournaments/pl/technicalGuide/running/)) grafana, loki and promtail are automatically launched. 
- Grafana is available at `http://localhost:3500`.
- The default login credentials are `admin` and `password`.

#### Grafana Configuration
	1.	Go to http://localhost:3500.
	2.	Log in to Grafana.
	3.	Navigate to “Configuration” -> “Data Sources” -> “Add data source”.
	4.	Select “Loki” and set the URL to http://chess-tournament-loki:3100.
	5.	Click “Save & Test”.