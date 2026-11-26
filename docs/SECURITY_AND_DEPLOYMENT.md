# Durcissement et déploiement Summury

## Version PHP requise

Le fichier `composer.lock` verrouille des dépendances Symfony qui exigent PHP 8.4.1 ou supérieur. Le projet déclare donc PHP `^8.4`, et Docker ainsi que la CI utilisent PHP 8.4. Vérifier que l'hébergement web fournit PHP 8.4 avant déploiement.

## Avant déploiement

1. Révoquer puis régénérer toute clé TMDB qui a été publiée dans le dépôt.
2. Copier `.env_default` vers `.env` et renseigner les variables de production.
3. Ne jamais committer `.env`, les exports SQL ou des identifiants.
4. Pour Docker Compose, renseigner également `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE`, `MYSQL_USER` et `MYSQL_PASSWORD`.
5. Exécuter `composer install --no-dev --optimize-autoloader` pour un déploiement PHP classique.

## Tests

Depuis le dossier `htdocs` :

```powershell
composer install
vendor\bin\phpunit
```

La CI GitHub exécute les tests PHPUnit et la vérification syntaxique PHP sur chaque push/PR vers `main`.

## Actions de sécurité incluses

- Les actions qui modifient des données passent par POST et les formulaires incluent le jeton CSRF.
- Les URL enregistrées sont limitées à HTTP/HTTPS.
- Les liens historiques non conformes sont neutralisés à l'affichage.
- Les requêtes sortantes vérifient les adresses IP résolues et limitent les réponses lues.
- La recherche média utilise des éléments DOM et `textContent` pour éviter l'injection HTML.
- La récupération du mot de passe limite les envois et les essais, hache le code et expire l'autorisation après validation.
- Les résultats de recherche média et l'état des signalements sont mis en cache.

## Docker

Lancer depuis le dossier `htdocs` après avoir complété `.env` :

```powershell
docker compose up -d --build
```

L'application est exposée sur `127.0.0.1:8080`. phpMyAdmin est optionnel et se lance avec le profil `tools` :

```powershell
docker compose --profile tools up -d
```

Le service MySQL et phpMyAdmin ne sont plus exposés sur toutes les interfaces réseau.
