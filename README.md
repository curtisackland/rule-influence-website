# rule-influence-website
Website to display which organizations are most successful in influencing regulators

# Getting Started
## Prerequisites
### Docker
Docker Desktop is recommended for monitoring the containers on Windows.
https://www.docker.com/products/docker-desktop/

### Node
Node Version Manager (nvm) is recommended for managing node installation.

1. Install nvm [Windows](https://github.com/coreybutler/nvm-windows/releases)/[Linux](https://github.com/nvm-sh/nvm#installing-and-updating)
2. Run
```bash
nvm install 18.18.0
nvm use 18.18.0
```
3. Verify that everything worked correctly
```bash
node -v
v18.18.0
```
```bash
npm -v
9.8.1
```

## Running The Site
Default database location is in `back-end/data/rulemaking_influence.db`
This file must exist for the application to work properly. 
You will need to add this file manually, it is not part of the repo.
You will also need to create our cache tables with the following command:

```bash
cd ./back-end
python3 generateCacheTables.py
```

All the services can be started in Docker by using the commands
```bash
cd ./docker
docker compose up -d --build
```
### Default Ports
| Port | Endpoint       |
|------|----------------|
| 80   | Front-end page | 
| 8080 | Back end api   |

## Development
[PhpStorm](https://www.jetbrains.com/phpstorm/) is the recommended editor for this project

When developing on the front-end, it is recommended to use vite's dev mode
```bash
cd ./front-end
npm run dev
```
This should start serving the site locally, the output of the command should show the port of the site.




