### ELK
Elasticsearch, Logstash, and Kibana are three tools that together form the Elastic Stack.
They are used for collecting, processing, visualizing, and analyzing data.
In our case, we use them to collect and visualize application logs.

- All application logs are collected in Elasticsearch.
- Application logs are processed by Logstash.
- Kibana is used to visualize application logs.

#### Accessing ELK
After starting the containers with the application ([see here](https://kkedzierski.github.io/chess-tournaments/pl/technicalGuide/running/))  Elasticsearch, Logstash, and Kibana will be automatically launched. 
- Kibana is available at `http://localhost:36505`.
- Elasticsearch is available at `http://localhost:9200`.
#### Grafana Configuration
	1.	Go to http://localhost:36505.
	2.	Navigate to the “Discover” section available in the left side menu.
	3.	Enter “chess-tournaments-logs-*” in the index pattern field and click “Next step”.
	4.	Select the “@timestamp” field as the event time and click “Create index pattern”.
	5.	In the Discover section, you can browse the application logs.

#### Enabling Security
- Create a `.env` file in the `docker` folder based on the `.env.dist` file.
- Set the environment variable values in the `.env` file:

```angular2html
ELASTICSEARCH_USERNAME=YourUserName
ELASTICSEARCH_PASSWORD=YourPassword
ELASTICSEARCH_SECURITY="true"
```

#### Changing the index name
- In the logstash configuration `docker/etc/logstash/conf.d/default.conf` change the value in the output object for the index value
```angular2html
output {
  elasticsearch {
    hosts => ["http://chess-tournaments-elasticsearch:9200"]
    index => Here
  }
}
```

#### Adding a new field to the logs
- In the logstash configuration `docker/etc/logstash/conf.d/default.conf` add a new file to the input object
```angular2html
  file {
    type => "test"
    path => "/var/www/chess-tournaments/log/test.log"
    start_position => "beginning"
  }
```

### Important
- It is necessary that in the docker-compose.yaml file the port for elasticsearch remains unchanged "9200:9200"
- Changing the name of the chess-tournaments-elasticsearch container should also be done in `docker/etc/logstash/conf.d/default.conf`
