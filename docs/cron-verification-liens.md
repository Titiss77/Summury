# Vérification automatique des liens

## Mise en place

Le worker HTTP `GET /j` traite au plus 20 cartes par appel et s'arrête après environ 12 secondes. Il stocke son curseur dans `item.link_checked_at`, sans toucher à `updated_at`. Les cartes sont reprises après 30 jours. Une requête planifiée répétée parcourt donc progressivement toute la base, sans charger l'ensemble des cartes dans PHP.

1. Définir dans le `.env` de production une clé longue et aléatoire : `cron.secret = "REMPLACER_PAR_UN_SECRET_ALEATOIRE_LONG"`.
2. Appliquer la migration CI4 `2026-11-17-000001_AddLinkCheckedAt`. Si les migrations CLI ne sont pas disponibles chez l'hébergeur, exécuter dans phpMyAdmin :

   ```sql
   ALTER TABLE item ADD COLUMN link_checked_at DATETIME NULL DEFAULT NULL;
   CREATE INDEX idx_item_link_checked_at ON item (link_checked_at);
   ```

3. Dans cron-job.org, créer une tâche GET toutes les 5 minutes vers `https://VOTRE-DOMAINE/j?token=VOTRE_SECRET`. Ne pas partager cette URL; le jeton peut apparaître dans les journaux HTTP du planificateur. Si l'outil permet les en-têtes, privilégier `X-Cron-Token: VOTRE_SECRET` et l'URL sans paramètre.
4. Le JSON `status=idle` indique que toutes les cartes éligibles ont été parcourues. `inconclusive` signifie que le réseau n'a pas permis de conclure; ces liens ne sont jamais déclarés morts à cause d'une erreur de transport.

La commande `php spark app:check-links` n'effectue plus de scan dangereux en mémoire; elle rappelle la configuration HTTP. Une tâche toutes les 5 minutes suffit pour drainer une file volumineuse; augmenter la fréquence seulement si le planificateur l'autorise.

## Règles de classement

Seuls une réponse HTTP finale 404/410 ou deux réponses NXDOMAIN (`Status=3`) indépendantes (Google DoH et Cloudflare DoH) marquent un lien mort. Timeouts, refus de connexion, erreurs TLS, réponses DoH incomplètes, 403, 429 et erreurs serveur restent non concluants. Les redirections sont suivies avec une limite. Un domaine parqué qui résout réellement vers un serveur public ne peut pas être identifié avec certitude par DNS seul; il faut une liste de pages de parking vérifiée manuellement pour éviter de classer à tort des sites valides.

## cURL et limites de l'hébergeur

Le worker conserve la validation TLS (`verify=true`), limite la connexion à 2 s et la requête totale à 4 s, suit au plus 3 redirections et envoie un User-Agent et des en-têtes Accept ordinaires. Ne pas désactiver la vérification des certificats et ne pas forcer une version SSL obsolète : cela affaiblit TLS sans contourner un pare-feu sortant. HTTP/2, HTTP/1.1, IPv4 ou d'autres User-Agent ne peuvent qu'aider certains sites particuliers; aucun réglage cURL ne peut garantir une sortie réseau lorsque Byethost bloque l'egress.

Pour une disponibilité supérieure, la vraie solution est de faire tourner le worker sur un hébergement/VPS dont les requêtes sortantes sont autorisées. Un service de cron externe déclenche le worker, mais ne fait pas les requêtes de vérification à la place du serveur web de l'application. La file est résistante aux timeouts 504, mais le débit effectif dépend des limites réseau et de la fréquence du planificateur.
