<?php

use Bibliotek\Utility\Auth;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigSingleton {
    private static $instance = null;
    private $twig;

    private function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/views'); // Adjust path as needed
        $this->twig = new Environment($loader, [
            'cache' => false,
        ]);
        // Add helper function to template engine
        $this->twig->addFunction(new TwigFunction('CurrentUser', function() {
            return Auth::currentUser();
        }));
        $this->twig->addFunction(new TwigFunction('CurrentURI', function() {
            return $_SERVER["REQUEST_URI"];
        }));
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->twig;
    }
}

