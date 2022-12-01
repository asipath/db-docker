# db-docker
Generate a Database as an image

## Introduction

This tool was written for Axelerant specific processes but can be used generally by overriding certain configuration. By default, you wouldn't be able to use this plugin if you cannot access Axelerant's GitLab repository. You may use it with your own Docker image registry or even with Docker Hub.

## Prerequisites

* Reasonably updated version of composer with recent version of PHP. Tested with composer 1.10.1 and composer 2+.
* Recent version of Docker.

### Optional requirements (only for default workflow)

By default, this plugin generates the image with name prefixed with Axelerant's Docker registry. The below requirements apply if you don't override the image name using configuration in [composer.json](#configuration).

* Ability to clone from gitorious.xyz.
* Logged in to Axelerant's GitLab Container Registry. To verify, run `docker login registry.gitorious.xyz`. Optional if you use the `--no-push` option.

## Installation

Install with composer into any Drupal project.

```bash
composer require --dev axelerant/db-docker
```

## Usage

Options may be specified using the command line or by specifying them in the `extra` section in your `composer.json` file. See the section below on [Configuration](#configuration) for more details.

```
$ composer db-docker --help
Usage:
  db-docker [options]

Options:
  -t, --docker-tag[=DOCKER-TAG]  The Docker tag to build
  -r, --git-remote[=GIT-REMOTE]  The git remote to use to determine the image name
  -s, --db-source[=DB-SOURCE]    Source of the database ("lando", "drush", or "file")
  -f, --db-file[=DB-FILE]        The path to the database file (required if db-source is set to file)
      --no-push                  Set to not push the image after building
  -h, --help                     Display this help message
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi                     Force ANSI output
      --no-ansi                  Disable ANSI output
  -n, --no-interaction           Do not ask any interactive question
      --profile                  Display timing and memory usage information
      --no-plugins               Whether to disable plugins.
  -d, --working-dir=WORKING-DIR  If specified, use the given directory as working directory.
      --no-cache                 Prevent use of the cache
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  Generate a Docker image for the database.
```

**Warning:** The above usage is generated by running `composer db-docker --help` and is generated by composer. Some options such as `--no-plugins` are not compatible. The option `--no-plugins` tells composer not to load any plugins, which means that `db-docker` won't be loaded at all.

### Examples

To let the plugin guess defaults, build the image, and push it.

```bash
composer db-docker
```

To explicitly specify a SQL file to build the image.

```bash
composer db-docker --db-source=file --db-file=<filename> # The file can either be plain SQL or gzipped.
```

## Configuration

The plugin also supports configuration via composer.json `extra` section.

```json
{
    "name": "drupal/site",
    // ...
    "require": {
        // ...
    },
    "require-dev": {
        // ...
        "axelerant/db-docker": "^1.0"
    },
    "extra": {
        "dbdocker": {
            "docker-image-name": "auto",
            "docker-tag": "auto",
            "docker-base": {
                "base-flavor": "bitnami",
                "image": "bitnami/mariadb:10.4",
                "user": "drupal8",
                "password": "drupal8",
                "database": "drupal8"
            },
            "git-remote": "origin",
            "db-source": "",
            "no-push": false
        }
    }
}
```

These options work the same way as the options available via the command line. Option values specified on the command line take precedence followed by the options in the `extra` section of `composer.json`. Finally, values are guessed as described below.

### Image details

By default, the image name and tag will be guessed using the method described below in the [Default Options](#default-options) section. You can override both of them using the `docker-image-name` and `docker-tag` settings in the configuration in `composer.json` as shown above. Additionally, you may also specify the `docker-tag` via the command line.

### Base image

You can use the `docker-base` configuration to specify the base image to use to build the database image. When not specified, the default image used is Bitnami's MariaDB 10.4 image with default access details which is compatible with [Lando's Drupal 8 recipe](https://docs.lando.dev/config/drupal8.html). You may override this to fit in your workflow as desired.

The base image details cannot be customized from the command line. They must be specified via the `composer.json`'s extra section.

### DDEV base image

The defaults for `docker-base` depend on the value of `base-flavor` setting. By default, the `base-flavor` is set to `bitnami` and the defaults are set as above. However, if `base-flavor` is `ddev`, the defaults change to the following:

```json
{
    "extra": {
        "dbdocker": {
            "docker-base": {
                "base-flavor": "ddev",
                "image": "drud/ddev-dbserver-mariadb-10.4:v1.17.0",
                "user": "db",
                "password": "db",
                "database": "db"
            }
        }
    }
}
```

These are the defaults required for building database images compatible for DDEV.

## Default options

The plugin tries to guess most values for input to correctly select the source, build the image, and push it.

### Determining the image name

The image name can be specified using the `docker-image-name` in `composer.json` as described above. If not specified (or set to `"auto"`), the image name is constructed from the git repository.

The image name is determined based on the git repository's `origin` remote (overridable using the `--git-remote` option). The remote URL should be a Git URL (not a HTTP URL) of type `git@gitorious.xyz:<group>/<project>.git`. For this, it would determine the image name `registry.gitorious.xyz/<group>/<project>/db`. See the next section for the image tag.

It's important to note that the image name is constructed only for Axelerant Gitlab repositories. If you want to use another Docker registry (such as Docker Hub or Quay.io), please use the option in `composer.json` to specify the proper name.

### Determining the image tag

The image tag, unless specified with the `--docker-tag` option, is assumed to be the current branch name. If the current branch is `master`, the image tag is used as `latest`.

### Determining the database source

These database sources are supported: `file`, `lando`, `ddev`, and `drush`. The source can be explicitly specified using the `--db-source` option. If not specified, the following rules are used to determine the source.
* If the `--db-file` option is present, then the source is set as `file`.
* If a file called `.lando.yml` is present, then the source is set as `lando`.
    * As an exception to above, the plugin attempts to detect if it is running inside a lando container. If so, the source is set to `drush`.
* If a directory called `.ddev` is present, then the source is set as `ddev`.
    * As an exception to above, the plugin attempts to detect if it is running inside a DDEV container. If so, the source is set to `drush`.
* If the above conditions fail, then the source is assumed to be `drush`.

In case the source is `lando`, `ddev`, or `drush`, the `drush sql:dump` command is used to obtain the SQL file. If the source is `lando` or `ddev`, then the drush command is executed inside the relevant container like so: `lando drush ...` or `ddev drush ...`.

## Reporting problems

If you see a bug or an improvement, create a pull request. For support, raise a request on Axelerant Slack #internal-support channel.
