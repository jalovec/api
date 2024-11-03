# Aplikace orders
## Application activation
- applicaion is in **PHP 8.1** and runs in **Docker**
- install docker to your computer. If you use Windows or iOS, install Docker desktop [Docker](https://docs.docker.com/engine/install/)
- open location where will be your project saved
- open terminal window from location
- run command `git init`
- run command `git clone https://github.com/jalovec/orders.git`
- after application clone is in folder
- go to folder orders `cd orders`
- run command `make create` that create docker containers
- run command make `db-diff` to be sure all migrations are ready
- run command make `db-migrate` to create database
- you can use te application at http://localhost:8000
- register your user and start