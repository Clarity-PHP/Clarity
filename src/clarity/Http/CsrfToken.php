<?php

namespace framework\clarity\Http;

class CsrfToken
{
    /**
     * @return string
     */
    public function generateToken(): string
    {
        try {

            return bin2hex(random_bytes(32));

        } catch (\Exception $e) {

            return uniqid('', true);
        }
    }

    /**
     *
     * @param bool $httponly
     * @param bool $https
     * @param int $expires
     * @return string
     */
    public function getToken(bool $httponly = true, bool $https = true, int $expires = 3600): string
    {
        $existingToken = $_COOKIE['csrf_token'] ?? null;

        if ($existingToken !== null) {
            return $existingToken;
        }

        $token = $this->generateToken();

        setcookie('csrf_token', $token, [
            'httponly' => $httponly,
            'secure' => $https,
            'samesite' => 'Strict',
            'expires' => time() + $expires,
        ]);

        return $token;
    }

    /**
     * @param string|null $csrfToken
     * @param string|null $cookieToken
     * @return bool
     */
    public function isValidCsrfToken(?string $csrfToken, ?string $cookieToken): bool
    {
        if ($csrfToken === null || $cookieToken === null) {
            return false;
        }

        return hash_equals($cookieToken, $csrfToken) === true;
    }
}