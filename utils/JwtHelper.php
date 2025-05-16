<?php
class JwtHelper {
    private static $secret = 'your_secret_key_here'; // In production, use a secure environment variable
    private static $algorithm = 'HS256';
    private static $issuer = 'task_api';
    private static $expiry = 3600; // 1 hour
    
    // Generate a JWT token
    public static function generateToken($payload) {
        $issuedAt = time();
        $expire = $issuedAt + self::$expiry;
        
        $tokenPayload = [
            'iat' => $issuedAt,     // Issued at
            'iss' => self::$issuer, // Issuer
            'nbf' => $issuedAt,     // Not before
            'exp' => $expire,       // Expiration
        ];
        
        // Merge custom payload with standard claims
        $tokenPayload = array_merge($tokenPayload, $payload);
        
        // Create JWT
        $header = self::base64UrlEncode(json_encode([
            'alg' => self::$algorithm,
            'typ' => 'JWT'
        ]));
        
        $payload = self::base64UrlEncode(json_encode($tokenPayload));
        $signature = self::generateSignature($header, $payload, self::$secret);
        
        return $header . '.' . $payload . '.' . $signature;
    }
    
    // Validate a JWT token
    public static function validateToken($token) {
        // Split token into header, payload and signature
        $parts = explode('.', $token);
        
        if (count($parts) != 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $parts;
        
        // Verify signature
        $valid = self::verifySignature($header, $payload, $signature, self::$secret);
        
        if (!$valid) {
            return false;
        }
        
        // Decode payload
        $payload = json_decode(self::base64UrlDecode($payload), true);
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    // Generate signature for JWT
    private static function generateSignature($header, $payload, $secret) {
        $signatureBase = $header . '.' . $payload;
        $hash = hash_hmac('sha256', $signatureBase, $secret, true);
        return self::base64UrlEncode($hash);
    }
    
    // Verify JWT signature
    private static function verifySignature($header, $payload, $signature, $secret) {
        $expectedSignature = self::generateSignature($header, $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
    
    // Base64Url encode
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    // Base64Url decode
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}