# 1. Installer le projet en local pour développer

[Suivant : 2. Architecture du projet](2-Architecture-du-projet.md)

La plateforme en-marche.fr est basée sur les outils open-source suivants :

- [Symfony](http://symfony.com/), framework PHP développé par la société française [SensioLabs](https://sensiolabs.com/fr)
- [Sass](http://sass-lang.com/), language étendant les possibilités de CSS
- [Webpack](https://webpack.github.io/docs/), aggrégateur de modules JavaScript
- [MariaDB](https://mariadb.org/), moteur de base de donnée dérivé de MySQL


## 1.1.1 Installer les pré-requis du projet

Le projet En Marche utilise [Docker](https://www.docker.com/), un outil de container permettant de mettre en
place un environnement de développement rapidement. Le projet n'a donc que trois prérequis :

- Docker (1.12+)
- docker-compose (1.10+)
- GNU make

Pour installer Docker, référez-vous à la documentation officielle adaptée à votre système d'exploitation :
https://docs.docker.com/engine/installation/.

Une fois Docker installé, pour vérifier son bon fonctionnement, exécutez `docker -v`, vous devriez obtenir quelque
chose comme suit :

```
$ docker -v
Docker version 1.12.4, build 1564f02
```

> Vous devez utiliser la version 1.12 minimum de Docker.

Pour installer docker-compose, référez-vous là aussi à la documentation officielle :
https://docs.docker.com/compose/install/.

Une fois docker-compose installé (installez-le globalement pour pouvoir y accéder depuis n'importe où),
pour vérifier son bon fonctionnement, exécutez `docker-compose -v`, vous devriez obtenir quelque chose comme suit :

```
$ docker-compose -v
docker-compose version 1.10.0, build 4bd6f1a
```

> Vous devez utiliser la version 1.10 minimum de docker-compose.

## 1.1.2 Préparer le projet pour développer

Le projet fonctionne grâce à différents containers Docker coordonnés par docker-compose. De plus, un Makefile vous
permettra d'exécuter les actions classiques lors du développement (vider le cache, fabriquer les assets, etc.).
Il faut donc que vous puissiez exécuter `make -v`, ce qui devrait vous donner quelque chose comme :

```
$ make -v
GNU Make 4.1
Construit pour x86_64-pc-linux-gnu
Copyright (C) 1988-2014 Free Software Foundation, Inc.
Licence GPLv3+ : GNU GPL version 3 ou ultérieure <http://gnu.org/licenses/gpl.html>
Ceci est un logiciel libre : vous êtes autorisé à le modifier et à la redistribuer.
Il ne comporte AUCUNE GARANTIE, dans la mesure de ce que permet la loi.
```

> **Note**: si vous utilisez Windows, nous vous recommandons très fortement d'utiliser la console Linux intgrée à
> Windows 10 (https://msdn.microsoft.com/fr-fr/commandline/wsl/install_guide) ou d'utiliser un émulateur de ligne de
> commande pour pouvoir utiliser `make` qui vous facilitera grandement le travail.
**Note**: Pour les utilisateurs de Windows, vous pouvez utiliser [l'équivalent Windows de make](http://gnuwin32.sourceforge.net/packages/make.htm)

Si Docker, docker-compose et make fonctionnent correctement, vous êtes prêt à préparer le projet pour développer.

### 1.1.2.1 Cloner le projet

Clonez votre fork du repository Git quelque part sur votre machine puis allez dans le dossier créé :

```
$ git clone git@github.com:<votre-fork>/en-marche.fr.git
$ cd en-marche.fr
```

Vous devriez alors pouvoir lancer `make` qui vous affichera l'aide du Makefile :

```
$ make
 Project setup
---------------------------------------------------------------------------
start:           Install and start the project
stop:            Remove docker containers
reset:           Reset the whole project
clear:           Remove all the cache, the logs, the sessions and the built assets
clean:           Clear and remove dependencies
cc:              Clear the cache in dev env

 Database
---------------------------------------------------------------------------
db:              Reset the database and load fixtures
db-diff:         Generate a migration by comparing your current database to your mapping information
db-migrate:      Migrate database schema to the latest available version
db-rollback:     Rollback the latest executed migration
db-load:         Reset the database fixtures

 Assets
---------------------------------------------------------------------------
watch:           Watch the assets and build their development version on change
assets:          Build the development version of the assets
assets-prod:     Build the production version of the assets

 Tests
---------------------------------------------------------------------------
test:            Run the PHP and the Javascript tests
tu:              Run the PHP unit tests
tf:              Run the PHP functional tests
tj:              Run the Javascript tests
lint:            Run lint on Twig, YAML and Javascript files
ls:              Lint Symfony (Twig and YAML) files
lj:              Lint the Javascript to follow the convention

 Dependencies
---------------------------------------------------------------------------
deps:            Install the project PHP and JS dependencies
```

### 1.1.2.2 Lancer l'initialisation du projet

Tout d'abord, créez une copie du fichier `docker-compose.override.yml.dist` appelée `docker-compose.override.yml`
afin de choisir le port à utiliser pour le projet.

Si vous avez décommenté la section `networks` de votre fichier `docker-compose.override.yml`, créez votre réseau `enmarche` :

```
$ docker network create enmarche
```

Lancez l'initialisation du projet avec `make start` :

```
$ make start          # Sous macOS ou si vous avez configuré votre utilisateur Linux pour Docker
$ sudo make start     # Sous Linux si vous avez simplement installé Docker
Pulling db (mariadb:latest)...
latest: Pulling from library/mariadb
...
```

Cela risque de prendre un peu de temps.

## 1.1.3 Accéder au site local

Dans votre fichier `/etc/hosts` :

```
127.0.0.1       enmarche.code
127.0.0.1       m.enmarche.code
127.0.0.1       legislatives.enmarche.code
```

Voici par defaut les noms de domaine pour les differentes applications, configurés dans `app/config/parameters.yml`.

```
env(APP_HOST): enmarche.code
env(AMP_HOST): m.enmarche.code
env(LEGISLATIVES_HOST): legislatives.enmarche.code
```

Le projet devrait être accessible sur
[http://enmarche.code:8000](http://enmarche.code:8000) (ou si vous êtes sous macOS, sur le port 8000 de la
machine virtuelle de votre instance Docker).

Si vous préférez accéder à l'application principale via [http://localhost:8000](http://localhost:8000) au lieu de [http://enmarche.code:8000](http://enmarche.code:8000) par exemple, il vous suffit d'ajuster le fichier `/etc/hosts` ainsi que le fichier `app/config/parameters.yml` et les parametres `env(APP_HOST)`, `env(AMP_HOST)` et `env(LEGISLATIVES_HOST)`.

### 1.1.3.1 Accéder à l'espace d'administration

Une fois le projet installé, vous pouvez accéder à l'espace d'administration en allant sur
[http://enmarche.code:8000/admin](http://enmarche.dev:8000/admin).

Vous pouvez alors y entrer les identifiants suivants :
```
admin@en-marche-dev.fr / admin        pour l'accès en mode administrateur
writer@en-marche-dev.fr / writer      pour l'accès en mode rédacteur
referent@en-marche-dev.fr / referent  pour l'accès en mode référent
```

### 1.1.3.2 Accéder à l'espace adhérent

Une fois le projet installé, vous pouvez accéder à l'espace adhérent en cliquant sur "Connexion".
Vous pouvez alors y entrer l'identifiant suivant :
```
jacques.picard@en-marche.fr / secret!12345
```

## 1.1.4 Services disponibles

Docker et docker-compose vous ont permis d'initialiser très rapidement le projet. Pour cela, docker-compose a
créé 4 containers :

- `app`, l'application en elle-même
- `db`, la base de donnée utilisée par l'application
- `redis`, une base de donnée clé-valeur en mémoire, utilisée en tant que cache de données
- `rabbitmq`, un système permettant de gérer des files de messages

Par défaut, si vous avez copié le `docker-compose.override.yml.dist` en `docker-compose.override.yml` vous avez access à des containers supplémentaires :
- `pma`, PHPMyAdmin, pour travailler avec la base de donnée
- `blackfire`, un profiler PHP

De plus vous devriez pouvoir accéder aux containers sur les ports suivants de votre machine locale :

- `app`, HTTP sur le port 8000
- `db`, PGSQL sur le port 5432
- `pma`, HTTP sur le port 8080
- `rabbitmq`, HTTP sur le port 15672

[Suivant : 2. Architecture du projet](2-Architecture-du-projet.md)
