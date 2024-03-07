# rule-influence-website
Website to display which organizations are most successful in influencing regulators

# Prerequisites
## Docker
Docker Desktop is recommended for monitoring the containers on Windows.
https://www.docker.com/products/docker-desktop/

For linux, docker can be installed by following the instructions [here](https://docs.docker.com/engine/install/ubuntu/#installation-methods).

# Running The Site
## 1) Add the database
Default database location is in `back-end/data/rulemaking_influence.db`
This file must exist for the application to work properly. 
You will need to add this file manually, it is not part of the repo.

## 2) Generate "cached" tables
This python script will add several tables to the database file.
These are used to make the site load the results more quickly.

Note: This query will take a long time, and the db file will be much larger
```bash
cd ./back-end
python3 generateCacheTables.py
```

## 3) Configuration
You will also need to make a .env file for the front-end like so:
```bash
cd ./front-end
cp .env.example .env
```
The .env configuration file may need to change based on configuration
- VITE_BACKEND_URL: This should be set to the URL/IP of the server hosting the backend service. localhost will work for local testing, but an IP address or FQDN will be needed for other people to be able to access the site.
## 4) Starting the services
All the services can be started in Docker by using the commands:
```bash
cd ./docker
docker compose up -d --build
```
To stop/remove the services:
```bash
cd ./docker
docker compose down
```



# Development
## Default Ports
| Port | Endpoint       |
|------|----------------|
| 80   | Front-end page | 
| 8080 | Back end api   |

## Node
Node Version Manager (nvm) is recommended for managing node installation.

1. Install nvm [Windows](https://github.com/coreybutler/nvm-windows/releases)/[Linux](https://github.com/nvm-sh/nvm#installing-and-updating)
2. Run
```bash
nvm install 18.18.0
nvm use 18.18.0
```
3. Verify that the installation worked correctly
```bash
node -v
v18.18.0
```
```bash
npm -v
9.8.1
```
## Editor
[PhpStorm](https://www.jetbrains.com/phpstorm/) is the recommended editor for this project

## Dev mode
When developing on the front-end, it is recommended to use vite's dev mode
```bash
cd ./front-end
npm run dev
```
This should start serving the site locally, the output of the command should show the port of the site.

## Front End Testing

When testing the front-end, the website must be up on http://localhost:5173/. 
```bash
cd ./front-end
npm test
```
It may be the case that you will need to run the following command to get test-specific dependencies.
```bash
npm install
```
If this does not work, run (in admin mode)
```bash
npm install selenium-webdriver mocha chai esm
```
