
# symfony-distributed-architecture-admin ![CI](https://github.com/giudicelli/symfony-distributed-architecture-admin/workflows/CI/badge.svg)

Symfony Distributed Architecture Admin is a Symfony bundle. It provides an administration interface for [symfony-distributed-architecture](https://github.com/giudicelli/symfony-distributed-architecture).

## Installation

```bash
$ composer require giudicelli/symfony-distributed-architecture-admin
```

After installing symfony-distributed-architecture-admin, or updating it, please make sure to run the following commands.

```bash
$ bin/console make:migration
$ bin/console doctrine:migrations:migrate
```

It will create or update all the necessary tables.

## Using

This controller allows you to see what's happening with your [symfony-distributed-architecture](https://github.com/giudicelli/symfony-distributed-architecture) processes.

### Configuration

Add a new route file in "config/routes/sda.yaml". This will allow you to acivate the controller and pick on which URL it will be available.

```yaml
sda:
    resource: '@DistributedArchitectureAdminBundle/Resources/config/routes.xml'
    prefix: /distributed-architecture/
```

Now you can access it on the "/distributed-architecture/" URL, you will need to be connected as an admin.

### Configuration on a remote server

On a remote server, you will also need to install symfony-distributed-architecture-admin, however you must not activate the route to the controller.

