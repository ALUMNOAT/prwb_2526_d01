# Projet PRWB 2526 - Groupe d01 - EPayFC

## Installation sous MacOS ou Linux

En développement, donnez les permissions d'écriture aux dossiers uploads et database :

* `sudo chmod -R 777 uploads`
* `sudo chmod -R 777 database`

## Notes de version itération 1

### Liste des utilisateurs et mots de passes

* boverhaegen@epfc.eu, password "Password1,", utilisateur
* mamichel@epfc.eu, password "Password1,", utilisateur
* quhouben@epfc.eu, password "Password1,", utilisateur
* xapigeolet@epfc.eu, password "Password1,", utilisateur

### Liste des bugs connus

* concurrence de sauvegarde vers la database: essayer de bid sur un item trop rapidement va lancer une erreur SQL
* /

### URLs deploiement

* étudiant Nom Prenom : Gorski Emilian
  * URL : http://infolab.epfc.eu:58333/prwb_2526_d01/login/index
  * Mot de passe : 30permacc
* étudiant Nom Prenom : Luca HULOT
  * URL : http://infolab.epfc.eu:58334/prwb_2526_d01/
  * Mot de passe : Chene365

### Liste des fonctionnalités supplémentaires

* Check IBAN implémenté (iter 1)
* Filtre par owner, titre de l'item, description, tout (iter 2)
* Nous avons pris en compte les caractères spéciaux pour la DB ("é", "€", "ù", etc...)


### Divers

* notre code est trop complexe, et certaines choses sont au mauvais endroit (à refactor)
* Au delà de ~100mb pas d'erreur de taille ne s'affiche, c'est à cause d'une histoire entre la vérification de taille fait côté serveur vs côté client (chrome). Par contre, avec une image de taille 10mb, l'erreur s'affiche correctement ! bug majeur ?

## Notes de version itération 2

* Comme nous avons des pagination, la recherche d'items ne se fait que côtés client... Nous n'avons pas compris comment faire pour créer un service de recherche. Il semblerais que nous devrions utiliser JsonSerializable, mais cela semble aller trop loin ? à voir, à refaire en itération 3
* La validation des items (Add ou Edit) ne se fait qu'en asynchrone, nous avions mal compris l'exercice au début mais n'avons pas le temps désormais. Nous allons corriger cela pour l'itération 3, et avons déjà commencé (voir le service "get_rules_service" dans model/Item, et voir que nous l'avons pris dans le script)
* Pas de modale de confirmation pour supprimer une image en AJAX
* Peu de CSS modifié, le "look" reste le même qu'à l'itération 1
* Nous n'avons pas fondamentalement changé les intéractions entre modèles, dommage

## Notes de version itération 3

...