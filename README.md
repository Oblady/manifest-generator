# manifest-generator

## Principes

Le manifestGenerator est un script php qui va générer un fichier de manifeste pour que les applications web puissent fonctionner hors connexion.
Il faut déclarer un fichier init.yml afin de connaître l'url du cache à générer..

## Construction du fichier init.yml

    manisfest_name : cache.appcache
    manifest_path : /media/pter/SSD-SOMCOM/oblady/deceuninck-appbrisevue-preview/
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

* **manifest_name** : Indique le nom de fichier du manifest. En règle générale cache.appcache
* **manifest_path** : Indique le chemin ou doit être déposé le manifest. Finir avec /.
* **domain** : Indique la localisation du ,site ceci permet de récupérer dans les entêtes l'ensemble des fichier js css
* **pages** : Liste l'ensemnble des pages à mettre en cache
* **resources** : Ce sont les images à mettre en cache. L'outil utilise finder de Symfony pour balayer les répertoires

## Documentation symfony de finder

[symfony finder documentation](http://symfony.com/doc/current/components/finder.html)

## modification du "layout"

Le fichier manifeste doit être déposé à la racine du site;

    <!DOCTYPE html>
    <html lang="en" ng-app="deceuninck-preview" manifest="cache.appcache">
    <head>
        <meta charset="UTF-8">
        <title>Deceuninck</title>
        <link href="build/components/angular-loading-bar/build/loading-bar.min.css" rel="stylesheet" />
        <link href="css/jquery.mCustomScrollbar.css" rel="stylesheet" />
        <link href="css/slick.css


