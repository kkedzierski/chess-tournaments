# Chess torunaments

## Full documentaion avaliable [here](https://kkedzierski.github.io/chess-tournaments/pl)

## Installation quick guide

### Requirements

To run this project, you need to have Docker and Docker Compose installed. Below are instructions on how to check if these tools are installed and how to install them if they are missing.

### Checking Docker Installation

To check if Docker is installed on your system, run the following command in your terminal:

```sh
docker --version
```

If Docker Compose is not installed, follow the instructions [on the official Docker Compose](https://docs.docker.com/get-docker/) website to install it.

### Checking Docker Compose Installation

To check if Docker Compose is installed on your system, run the following command in your terminal:

```sh
docker-compose --version
```

If Docker Compose is not installed, follow the instructions [on the official Docker Compose](https://docs.docker.com/compose/install/) website to install it.

### Running the project

To run the project, clone the repository and run the following commands in the project directory:

1. clone the repository

```sh
git clone git@github.com:kkedzierski/chess-tournaments.git
cd chess-tournaments
```

2. Run the project

```sh
bash docker/run-app.sh
```