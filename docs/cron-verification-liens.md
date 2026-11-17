# Scan Ping en un clic

Depuis la page **Administration > Liens morts**, cliquez sur **Lancer le scan Ping**. Le serveur reconstruit automatiquement les origines uniques dans `writable/logs/liste.log` à partir de la base; la page demande ensuite un test ICMP à Check-Host pour chacune, puis enregistre les résultats. Aucun script, téléchargement, PowerShell ou logiciel à installer sur le poste administrateur.

Gardez la page ouverte jusqu’au message de fin. Les tests sont espacés pour respecter les limites du service; un grand nombre de domaines peut donc prendre plusieurs minutes. Les erreurs temporaires et résultats ambigus restent `unknown` et ne sont pas marqués morts. Un domaine est déclaré mort seulement si les nœuds de test retournent de façon répétée un échec de ping. Certains sites filtrent volontairement ICMP, même s’ils fonctionnent en HTTPS: ces cas peuvent donc être classés morts selon le critère demandé.

Le test part des nœuds externes de Check-Host, pas du serveur Byethost ni du PC. Les noms de domaine de la liste sont transmis à ce service tiers. Son API est asynchrone (création du ping puis interrogation du rapport) et applique des limites de fréquence pour les appels anonymes: [documentation API Check-Host](https://check-host.cc/docs), [limites d’utilisation](https://check-host.cc/about).

Le scan ne se déclenche pas au chargement du site; il démarre uniquement avec le bouton de la page d’administration.
