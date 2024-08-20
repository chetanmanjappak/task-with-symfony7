## Introduction

This is a Symfony application for a simple rule engine. It includes a Symfony skeleton and a Docker environment configured with the following services:

- RabbitMQ
- MySQL (available locally at port 3307, Docker services use port 3306)
- MailHog (UI available locally at port 8025)
- PHP
- Nginx (available locally at port 8888; your API endpoints will be accessible through here)
- my-php-cron-image
- backend-home-task_messenger-consumer_1

## How to Use the Docker Environment

### Build the PHP Docker Image

First, build the Docker image:
`docker build -t my-php-cron-image .`

A cron job is set up to monitor the scanning status and runs every 5 minutes. You can modify the interval by editing the `cron/process-scan` file. Don’t forget to rebuild the image after making changes.

Before starting the Docker environment, make sure to update the following environment variables in your `.env` file:

- `DEBRICKED_USER`
- `DEBRICKED_PWD`
- `SLACK_DSN`

### Start the Environment

Run the following command to start the environment:
`docker compose up`

### Access PHP Environment's Shell

You can access the PHP environment's shell by executing:

`docker compose exec php bash`

Make sure the environment is up and running before executing this command.

### Run PHP Commands Inside the Container

**Install Dependencies**

Ensure Composer is installed in the container, then run:

`composer update`

**Migrate the Database**

```bash
php bin/console doctrine:migrations:migrate
php bin/console cache:clear
```

**Check Background Process Logs**

To check the background process, run:

```bash
docker-compose logs -f messenger-consumer
```

**Check General Logs**

To check general logs, run:

```bash
docker-compose logs -f
```

**Check Cron Logs**

To check cron logs, run:

```bash
docker-compose logs -f cron
```

Alternatively, check the logs directly in the cron container:

```bash
docker compose exec cron cat /var/log/cron.log
```

Replace `email`, `slack_channel`, and `batch_name` with your values.
The batch name referes bach and repo name as well in debricked

You can check the emails at [MailHog UI](http://localhost:8025/).

### Development Overview

#### API Creation

- Developed an API for uploading multiple files. Below is an example `cURL` command you can use in Postman to interact with this API:
  ```bash
  curl --location 'http://localhost:8888/api/upload-files' \
    --form 'email="chetan.majappa@gmail.com"' \
    --form 'slack_channel="simple-rule-engine"' \
    --form 'files[]=@"/path/to/file"' \
    --form 'batch_name="Batch1"'
  ```
- The fields `email`, `files`, and `batch_name` (also referred to as repository name) are mandatory.

### Process Overview

- Upon receiving a request, the API will create entries in the related tables (refer to the table structure and relationships in `src/Entity/`), temporarily store the files, and dispatch them to a background process with RabbitMQ for forwarding the files to Debricked. The API will respond with:
  ```json
  {
    "status": "success",
    "message": "Files uploaded and processing started."
  }
  ```
- The background process will handle file uploads and scanning, and trigger email and Slack notifications for the following events:
  - When upload progress starts
  - If any file updates fail
- Once the upload is complete, the Debricked API response data will be stored in the scan table (e.g., `ciUploadId`, `uploadProgramsFileId`, etc.). The file will then be deleted from the server storage.

### Status Tracking

- The status of each process is maintained in the relevant tables.
- Once scan initiation is completed, the status in the scan table will be updated to "SCANNING-IN-PROGRESS".

### Cron Job

- A cron job runs every 5 minutes. This job fetches `ciUploadId` values from the scan table where the status is "SCANNING-IN-PROGRESS" and checks the scanning status using the Debricked API. If the progress reaches 100%, the status will be updated to "SCANNING-COMPLETED". If `vulnerabilitiesFound` exceeds 4 (configurable in `src/MessageHandler/ProcessScanMessageHandler.php` line 72), email and Slack notifications will be triggered.

### Stop the Environment

To stop and remove the containers, run:

```bash
docker compose down
```
**If you face any problems, please send your queries to** 
[chetan.manjappa@gmail.com](mailto:chetan.manjappa@gmail.com).
