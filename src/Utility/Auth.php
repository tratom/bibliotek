<?php

namespace Bibliotek\Utility;

use Bibliotek\Entity\User;
use Bibliotek\Foundation\User as FoundationUser;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {

    private const SECRET_KEY = "U423cybUDGFVgjRdLnpj";
    private const ISSUER = "bibliotek.tragno.cc";
    private const AUDIENCE = "bibliotek";

    public static function issueJWT($userId, $userRole = 'user') {
        $now = time();
        $payload = [
            'iss' => self::ISSUER,
            'aud' => self::AUDIENCE,
            'iat' => $now,
            'exp' => $now + 3600, // valid 1 hour
            'sub' => $userId,
            'role' => $userRole,
        ];

        return JWT::encode($payload, self::SECRET_KEY, 'HS256');
    }

    public static function isValidJWT($jwt) {
        try {
            JWT::decode($jwt, new Key(self::SECRET_KEY, 'HS256'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getTokenPayload($jwt) {
        try {
            $decoded = JWT::decode($jwt, new Key(self::SECRET_KEY, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function currentUser() {
        if (!isset($_COOKIE['jwt'])) {
            return null;
        }
        $jwt = $_COOKIE['jwt'];
        if (!self::isValidJWT($jwt)) {
            return null;
        }
        $payload = self::getTokenPayload($jwt);
        $user = FoundationUser::findUser($payload['sub']);
        return $user;
    }
}
