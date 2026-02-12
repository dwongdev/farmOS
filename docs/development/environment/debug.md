# Debugging

The farmOS development Docker image comes pre-installed with
[XDebug](https://xdebug.org) 3, which allows debugger connections on port 9003.

XDebug can be configured to discover the client host automatically with the
following `extra_hosts` and `environment` configuration in `docker-compose.yml`:

    extra_hosts:
    - host.docker.internal:host-gateway
    environment:
      XDEBUG_MODE: debug
      XDEBUG_CONFIG: client_host=host.docker.internal
      XDEBUG_SESSION: 1
      PHP_IDE_CONFIG: serverName=localhost

## IDEs

### PHPStorm

In PHPStorm, enable the "Start listening for PHP Debug Connections" option, add
a breakpoint in your code, load the page in your browser, and you should see a
prompt appear in PHPStorm that will begin the debugging session and pause
execution at your breakpoint.

## Drush commands

Drush disables XDebug by default. Run with the `--xdebug` flag to enable it.

For example: `drush --xdebug status`
