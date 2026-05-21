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

### Partie A

Bon ... je vais etre franc. Je pense que je ne suis pas pret.

J'ai commencé à 18:30. Terminé à 20:30. 2 heures c'est trop. Ca devrais prendre pas plus de 30 minutes. 1 heure max
J'ai eu du mal niveau requete SQL: 
- Je voulais juste fetch() au lieu de fetchAll().
- fetch array -> [Objet, Objet, Objet] -> boucle -> prendre les données de chaque objet
- type de retour sur le SQL
J'ai déja mis une méthode delete/persist et autres choses alors que pas besoin.
Niveau logique, j'étais bien. Mais au lieu d'ecrire du code, j'étais en train de copier coller comme un con. Alors que c'etait hyper facile.
View : j'ai fait "ul" au lieu de "li", 1ere erreur 
Controlleur : aucune erreur
Model (Categories et ItemCategories): difficulté niveau transformation de la réponse SQL vers un array -> new Object

J'avais la logique, j'ai plus struggle sur le PDO et création du retour

Type de retour  | Signification                        | Exemple typique
----------------|--------------------------------------|-----------------------
void            | Action pure, pas de donnée à rendre. | delete(), set_name()
static / self   | Renvoie l'objet modifié/créé.        | persist()
array           | Renvoie une collection d'objets.     | get_all_categories()
Object|false    | Renvoie un résultat unique ou rien.  | get_member_by_pseudo()

Je pense que le reste va etre 10x mieux. J'ai plus codé PHP depuis ... longtemps. 

### Partie C

J'ai commencé à 20:00. Fini 22:00 (pas de js encore)

La plus grosse difficulté était le controller->view. 
Et comme un con je voulais stocker tout l'objet au lieu de l'id et envoyer (et je voulais stocker les noms comme un con aussi).
J'ai du aussi chercher la methode in_array de PHP (J'ai utilisé l'IA car je savais que je dois avoir 2 boucles mais j'savais pas comment comparer 2 tableaux.).

La modale terminé
Regle facile, modale basique aussi
Probleme avec 'input[name="categories\\[\\]"]' , sinon ok. Dur de debugger

### Partie B




