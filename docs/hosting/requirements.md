# farmOS Server Requirements

**Note:** These requirements are for hosting farmOS on a live production server.
For local development/testing, please refer to the
[development environment](/development/environment) documentation.

Docker is the recommended method of hosting farmOS because it encapsulates the
web server requirements described below. For more information, refer to the
[farmOS in Docker](/hosting/docker) documentation.

## Web server

farmOS is based on [Drupal](https://drupal.org), and therefore shares many of
the same [requirements](https://drupal.org/docs/system-requirements).

In addition to Drupal's basic requirements, farmOS has the following server
dependencies. The [farmOS Docker images](/hosting/docker/) include these.

- **PHP 8.3+**
- **PHP configuration** - The following PHP settings are recommended:
    - `memory_limit=256M`
    - `max_execution_time=240`
    - `max_input_time=240`
    - `max_input_vars=5000`
    - `realpath_cache_size=4096K`
    - `realpath_cache_ttl=3600`
- **[PHP BCMath extension](https://www.php.net/manual/en/book.bc.php)** and
  **[GEOS](https://trac.osgeo.org/geos)** are required for accurate geometric
  calculations. farmOS can be installed without these, but production usage
  without them is strongly discouraged.
- **[PHP EXIF extension](https://www.php.net/manual/en/book.exif.php)** is
  necessary for automatic image rotation, but is not required.

## Database server

A database server needs to be provisioned that farmOS can connect to.
PostgreSQL is recommended. MySQL/MariaDB and SQLite are also supported.

This can be installed on the same server as farmOS (either directly or in a
Docker container), or it can be on a separate server.

Minimum version requirements:

- PostgreSQL 16+
- MariaDB 10.6+
- MySQL/Percona 8.0+
- SQLite 3.45+

See [farmOS in Docker][/hosting/docker/] for an example configuration that runs
a PostgreSQL database Docker container alongside a farmOS container.

## SSL

Although not strictly a requirement, some features (like the "Geolocate" button
on maps) will only work over a secure connection. SSL is also recommended if you
are streaming sensor data into farmOS, to keep your sensor's private key a
secret.

A common strategy is to use [Nginx](https://nginx.org) as a reverse proxy with
SSL termination, which listens on port 443 and forwards to farmOS on port 80.
[Let's Encrypt](https://letsencrypt.org) is a good option for free SSL
certificate issuance, and renewal can be automated via cron.

These resources may be helpful:

- [Drupal HTTPS Information](https://www.drupal.org/https-information)
- [Reverse Proxy Forum Post](https://farmos.discourse.group/t/running-behind-reverse-proxy/108) -
  Includes links to related GitHub issues and examples of how others have
  configured reverse proxies serving HTTPS.
- [Local HTTPS](/development/environment/https) - Documentation for running an
  Nginx reverse proxy with self-signed certificates for local farmOS
  development with HTTPS.

## Satellite map layers

farmOS includes an optional [Mapbox](https://www.mapbox.com) module that can be
enabled to add satellite imagery layers to the map. A Mapbox API key is
required. For more information, see Mapbox's official documentation:
[Access tokens](https://docs.mapbox.com/help/how-mapbox-works/access-tokens).
Enable the Mapbox module at Setup > Modules, and then add the API key at
Setup > Settings > Map > Mapbox.
