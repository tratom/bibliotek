<?php
if (!session_id()) @session_start();
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require_once "vendor/autoload.php";
require_once __DIR__ . '/TwigSingleton.php';

// Create a simple "default" Doctrine ORM configuration for Attributes
$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: array(__DIR__."/src/"),
    isDevMode: true,
);

$dsnParser = new DsnParser();
$connectionParams = $dsnParser
    ->parse('mysqli://root:bibliotek@mysql:3306/bibliotek');
$connection = DriverManager::getConnection($connectionParams);
// obtaining the entity manager
global $entityManager;
$entityManager = new EntityManager($connection, $config);

// Get the Twig instance
global $twig;
$twig = TwigSingleton::getInstance();

global $msg;
$msg = new \Plasticbrain\FlashMessages\FlashMessages();
// Set HTML properties according to Tabler template
$msg->setCloseBtn('<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>');
$msg->setMsgCssClass('alert alert-dismissible');