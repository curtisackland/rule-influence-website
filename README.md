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
The .env configuration file may need to change based on configuration:
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

## 5) Visit the site
The site should be available at the URL/IP of the server hosting the front-end services http://your.ip.or.url/

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
This should start serving the site locally on ```localhost:5173```, the output of the command should show the port of the site.

## Project Layout and Technologies with Resources

The front-end is made using the [VueJS3](https://vuejs.org/guide/introduction) framework along with [Vuetify 3](https://vuetifyjs.com/en/getting-started/installation/#installation)
and [Bootstrap](https://getbootstrap.com/docs/5.0/getting-started/introduction/). 

The back-end is made using [PHP Slim](https://www.slimframework.com/docs/v4/) to create endpoints and [PHP's PDO](https://www.php.net/manual/en/book.pdo.php) to connect and interact with
the database to query data.

The app is containerized into [Docker](https://www.docker.com/) containers. They are built together using [Docker Compose](https://docs.docker.com/compose/).

### Front-end

To see the front-end pages go to ```front-end/src/views```. These are all the different pages through the website. Some are made up of custom components that you can see in ```front-end/src/components```.
Note: you will be able to see changes in the front-end immediately when running ```npm run dev```, however you will have to rebuild the docker container with ```cd ./docker && docker compose up -d --build```
to see changes when not running in dev mode. 

#### Adding a Page

To add a page, create a new Vue component/file in ```front-end/src/views``` (you can use the other views/pages for reference). Then in ```front-end/src/router/index.js```, add a new route
similar to the other routes but changing the component it routes to that of the new component/page you just created. Also, update the path to something you want the page
to be accessible with and update the name to something that makes sense for the route. If you'd like to be able to access this page from the navigation bar as well, go to 
```front-end/src/components/NavBar.vue``` and update the variable array, ```menuItems```, with the new route (should be done similar to the other routes that exist in the array).
And that's it, you should now have a new page when you go to ```localhost:5173/<path to new page>```, where ```localhost:5173``` can be replaced with whatever your website address is.

### Back-end

To see all the back-end routes for the endpoints where data is retrieved from, go to ```back-end/src/index.php```. There will be a list of routes that point to different PHP classes
that handle retrieving data for that endpoint. To see these PHP classes, go to ```back-end/src/GetInfoActions```.

#### Adding an endpoint

To add an endpoint, add a new php class/file under ```back-end/src/GetInfoActions```. Copy the layout from another class (GetHomePageInfo for example) and update the queries and filters
with your own queries and filters. Add a new route in ```back-end/src/index.php``` similar to the other routes listed there and make sure that it uses your new PHP class.
And that's it, you should be able to access data from your new endpoint at ```localhost:8080/<new route name>```, where ```localhost:8080``` can be replaced with whatever your website address is.

#### Database Cache Table Creation Script

You can see all the cache tables we use for each endpoint in ```back-end/generateCacheTables.py```.

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
