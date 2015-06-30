.. Manifest Generator documentation master file, created by
   sphinx-quickstart on Mon Jun 29 12:23:40 2015.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Manifest Generator
==================

Contents:

.. toctree::
   :maxdepth: 2

Principes
*********

Le manifestGenerator est un script php qui va générer un fichier de manifeste pour que les applications web puissent fonctionner hors connexion.
Il faut déclarer un fichier init.yml afin de connaître l'url du cache à générer..


Utilisation de Manifest Generator
*********************************

Construction du fichier init.yml::

    manisfest_name : cache.appcache
    domain : http://localhost:9876/
    pages:
      - "#/1"
      - "#/2"
    resources:
      realPath: "/media/pter/SSD-SOMCOM/oblady/deceuninck-appbrisevue-preview/app/"
      finalPath : ""
      paths:
        - images

    sql:
      - "SELECT * FROM table"

Indique le nom de fichier du manifest. En règle générale cache.appcache

domain : Indique la localisation du ,site ceci permet de récupérer dans les entêtes l'ensemble des fichier js css

pages: Liste l'ensemnble des pages à mettre en cache

resources : Ce sont les images à mettre en cache. L'outil utilise finder de Symfony pour balayer les répertoires

Documentation symfony de finder `symfony finder documentation`_

.. _symfony finder documentation: http://symfony.com/doc/current/components/finder.html


Le fichier manifeste doit être déposé à la racine du site::

    <!DOCTYPE html>
    <html lang="en" ng-app="deceuninck-preview" manifest="cache.appcache">
    <head>
        <meta charset="UTF-8">
        <title>Deceuninck</title>
        <link href="build/components/angular-loading-bar/build/loading-bar.min.css" rel="stylesheet" />
        <link href="css/jquery.mCustomScrollbar.css" rel="stylesheet" />
        <link href="css/slick.css




Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`

