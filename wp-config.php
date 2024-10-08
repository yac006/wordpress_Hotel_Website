<?php
define( 'WP_CACHE', true );
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link https://fr.wordpress.org/support/article/editing-wp-config-php/ Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'hotel_website_db' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost:3308' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'sC-hT?Z>epwjjo!6$,(q4:;$5?lkuu_MV.-cqAM~n:)=+@tR~[gY?}7_6*kDU|)q' );
define( 'SECURE_AUTH_KEY',  '{,qbk7#ZGDUaYe %iSm_S:[y~R/#[fe6]eO_d<5R&8,M^a|xcI2L2KjF.s+6~si4' );
define( 'LOGGED_IN_KEY',    'pJa$fbY?H8L7o=yHtjR)[i+2C^~*pOVqi(ttv/<yBsKA,u37o(y[U>Ft<NE+f&aq' );
define( 'NONCE_KEY',        'PVFVnyhCB7Q+Bp$Cj.<|BG]Jr}cClEc61zNAHG,+agqbcL_)#iHD.w76)hcOA2MR' );
define( 'AUTH_SALT',        '<5H=3b=SH]T)-YQ`1}SVz4?8IclowpY}{id|L{8RtBDc?Y*oeZ]-~mt}p=(T++*N' );
define( 'SECURE_AUTH_SALT', 'HE_[{(l7%ZK,|<<Ys0){u8Tm`:{4/bBFe&GN7#yOJJM&3P(e`k^MR~w<$$/yv|Hy' );
define( 'LOGGED_IN_SALT',   'I<IMW60u7ddT91y|oalX^mz3F%jnv.hUvrLyFX_B&&Q`CQDVgl4Kdn6wLc $j,j?' );
define( 'NONCE_SALT',       'jtglWCh!pj6V.t$V5B*YxFg)I4cI/B/a%+#/A05C[G&=.@!|nl@wc8Y?O=q[c`Y=' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs et développeuses : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs et développeuses d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur la documentation.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');
