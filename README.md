
# symfony-distributed-architecture-admin ![CI](https://github.com/giudicelli/symfony-distributed-architecture-admin/workflows/CI/badge.svg)

Symfony Distributed Architecture Admiin is a Symfony bundle. It provides an administration interface for [symfony-distributed-architecture](https://github.com/giudicelli/symfony-distributed-architecture).

## Installation

```bash
$ composer require giudicelli/symfony-distributed-architecture-admin
```

## Using

This controller allows you to see what's happening with your [symfony-distributed-architecture](https://github.com/giudicelli/symfony-distributed-architecture) processes. To access it, you will need to be connected as an admin.

### Configuration

Add a new route file in "config/routes/sda.yaml". This will allow you to acivate the controller and pick on which URL it will be available.


```yaml
sda:
    resource: '@DistributedArchitectureAdminBundle/Resources/config/routes.xml'
    prefix: /distributed-architecture/
```

Now you can access it on the "/distributed-architecture/" URL.