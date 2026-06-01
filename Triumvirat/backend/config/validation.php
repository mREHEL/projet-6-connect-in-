<?php

return [
    /**
     * LISTE STRICTE : Pour les identifiants (username, identifiants système)
     */
    'forbidden_identifiers' => [

        // Rôles système
        'admin',
        'administrator',
        'moderator',
        'mod',
        'support',
        'staff',
        'official',
        'system',
        'root',
        'webmaster',
        'owner',
        'superadmin',

        // Termes techniques
        'bot',
        'api',
        'null',
        'undefined',
        'deleted',
        'guest',
        'anonymous',
        'user',
        'test',

        // Insultes graves
        'fuck',
        'shit',
        'nigger',
        'faggot',
        'connard',
        'salope',
        'encule',
        'fdp',
    ],

    /**
     * LISTE MODÉRÉE : Pour le contenu utilisateur (posts, bio, commentaires)
     */
    'forbidden_content' => [

        // Insultes très graves
        'fuck',
        'shit',
        'bitch',
        'asshole',
        'cunt',
        'connard',
        'salope',
        'encule',
        'pute',
        'fdp',

        // Discriminations raciales/homophobes
        'nigger',
        'nigga',
        'kike',
        'faggot',
        'tranny',
        'bougnoule',
        'negre',
        'pd',

        // Contenu adulte explicite
        'porn',
        'xxx',
        'hentai',
        'gangbang',

        // Spam/Scam évidents
        'freegift',
        'clickhere',
        'youwin',
        'winner',
    ],

    /**
     * LISTE LÉGÈRE : Pour les emails (validation de base)
     */
    'forbidden_email_terms' => [
        'admin',
        'support',
        'staff',
        'official',
        'noreply',
        'no-reply',
        'system',
        'bot',
        'fuck',
        'shit',
        'porn',
        'sex',
        'root',

    ],

    /**
     *  LISTE ULTRA-STRICTE : Pour les mots de passe
     */
    'forbidden_passwords' => [
        'password',
        'Password',
        'PASSWORD',
        '12345678',
        '123456789',
        'qwerty',
        'azerty',
        'welcome',
        'admin',
    ],
];