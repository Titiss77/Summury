<?php return [
    'invalidExpiresTime' => 'Type "{0}" invalide pour l\'attribut "Expires". Attendu : string, integer, objet DateTimeInterface.',
    'invalidExpiresValue' => 'L\'heure d\'expiration du cookie n\'est pas valide.',
    'invalidCookieName' => 'Le nom du cookie "{0}" contient des caractères invalides.',
    'emptyCookieName' => 'Le nom du cookie ne peut pas être vide.',
    'invalidSecurePrefix' => 'L\'utilisation du préfixe "__Secure-" nécessite de définir l\'attribut "Secure".',
    'invalidHostPrefix' => 'L\'utilisation du préfixe "__Host-" nécessite le flag "Secure", aucun attribut "Domain", et "Path" défini sur "/".',
    'invalidSameSite' => 'La valeur SameSite doit être None, Lax, Strict ou une chaîne vide, "{0}" donné.',
    'invalidSameSiteNone' => 'L\'utilisation de l\'attribut "SameSite=None" nécessite de définir l\'attribut "Secure".',
    'invalidCookieInstance' => 'La classe "{0}" attendait un tableau de cookies de type "{1}" mais a reçu "{2}" à l\'index {3}.',
    'unknownCookieInstance' => 'L\'objet Cookie nommé "{0}" et de préfixe "{1}" n\'a pas été trouvé dans la collection.',
];