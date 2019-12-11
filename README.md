# SeanMorris\RouteTest

This project exposes a REST interface to a Redis store for the purposes of storing & retrieving patient metrics.

Communitation is done via JSON in both directions, meaning the system expects raw json in the POST body. Plain strings must be quoted.

More information/documentation can be found in the PostMan integration:

https://documenter.getpostman.com/view/9758489/SWE84csh?version=latest

## Deployment

Once you've installed the three programs below, Simply clone the project and navigate to the directory. Run the following commands to get everything up and running:

```bash
$ make build        # build your images locally
$ # OR
$ make pull-images  # pull prebuilt images from dockerhub 

# THEN

$ make start        # start the project
# OR
$ make start-fg     # start the project in the foreground
```

You can then run the following command to generate 1000 test records:

```bash
$ make fill
```

You can also stop the project services with one command:

```bash
$ make stop
```

## Dependencies

All the dependencies, their versions & configurations are handled by docker. You'll only need a few tools listed below to actually interact with the project. There's no need to search the web for the right version of a given dependency to run the project.

The only dependencies are **Docker, Docker-Compose & Make**. Installation instructions are available at the following links:

* https://docs.docker.com/install/
* https://docs.docker.com/compose/install/
* https://tecadmin.net/install-development-tools-on-debian/

The following technologies are used under the hood:

* Apache & mod_rewrite
* PHP 7.3
* Pecl
* Redis
* Redis extension for PHP

## HTTP Interface

Additional documentation & PostMan integration can be found here:

https://documenter.getpostman.com/view/9758489/SWE84csh?version=latest

### Patient-level Methods

`GET /patients`

List all patients.

`GET /patients/[ID_NUMBER]`

Get the details of a single patient.

`POST /patients/[ID_NUMBER]` <- [JSON object containing the patient's metrics]

Create a new patient with the given metrics.

`PATCH /patients/[ID_NUMBER]` <- [JSON object containing the patient's metrics]

Update an existing patient with the given metrics.

`DELETE /patients/[ID_NUMBER]`	

Delete an existing patient.

### Metric-level Methods

`GET /patients/[ID_NUMBER]/metrics`

List all metrics for a given patient.

`GET /patients/[ID_NUMBER]/metrics/[METRIC_NAME]`

Get a specific metric for a given patient.

`POST /patients/[ID_NUMBER]/metrics` <- [JSON object containing the patient's metrics]

Create multiple metrics on a given patient.

`PATCH /patients/[ID_NUMBER]/metrics/[METRIC_NAME]` <- [JSON STRING containing the patient's metrics]

Update a specific metric for a given patient.

`DELETE /patients/[ID_NUMBER]/metrics/[METRIC_NAME]`

Delete a specific metric for a given patient.

