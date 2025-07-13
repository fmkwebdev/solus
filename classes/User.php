<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// A Composer autoloader betöltése
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ------------------------------------------------------------------
     *  REGISZTRÁCIÓ E‑MAIL AKTIVÁLÁSSAL
     * ----------------------------------------------------------------*/
    public function register(
        string $email,
        string $username,
        string $password,
        bool   $isWalker = false       // későbbi checkboxhoz
    ): bool {
        if ($this->emailExists($email) || $this->usernameExists($username)) {
            return false;
        }

        $hash           = password_hash($password, PASSWORD_BCRYPT);
        $activationCode = bin2hex(random_bytes(16));  // 32 karakter

        $stmt = $this->pdo->prepare(
            "INSERT INTO users (email, username, password, is_walker, activation_code)
             VALUES (:email, :username, :password, :is_walker, :activation_code)"
        );

        $ok = $stmt->execute([
            ':email'           => $email,
            ':username'        => $username,
            ':password'        => $hash,
            ':is_walker'       => $isWalker,
            ':activation_code' => $activationCode
        ]);

        if ($ok) {
            $this->sendActivationEmail($email, $activationCode);
        }
        return $ok;
    }

    /* ------------------------------------------------------------------
     *  BEJELENTKEZÉS – csak aktív fiókoknál
     * ----------------------------------------------------------------*/
    public function login(string $email, string $password): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            $user &&
            password_verify($password, $user['password']) &&
            (int)$user['is_active'] === 1               // aktiválva?
        ) {
            return $user;
        }
        return null;
    }

    /* ------------------------------------------------------------------
     *  AKTIVÁCIÓS KÓD FELDOLGOZÁSA
     * ----------------------------------------------------------------*/
    public function activate(string $code): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM users
             WHERE activation_code = :code AND is_active = 0 LIMIT 1"
        );
        $stmt->execute([':code' => $code]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        $update = $this->pdo->prepare(
            "UPDATE users
             SET is_active = 1, activation_code = NULL
             WHERE id = :id"
        );
        return $update->execute([':id' => $user['id']]);
    }

    /* ------------------------------------------------------------------
     *  SEGÉD FÜGGVÉNYEK
     * ----------------------------------------------------------------*/
    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM users WHERE email = :email"
        );
        $stmt->execute([':email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM users WHERE username = :username"
        );
        $stmt->execute([':username' => $username]);
        return (bool)$stmt->fetchColumn();
    }

    /* ------------------------------------------------------------------
     *  E‑MAIL KÜLDÉS PHPMailer‑rel
     * ----------------------------------------------------------------*/
    private function sendActivationEmail(string $email, string $code): void
    {
        $mail = new PHPMailer(true);

        try {
            // ===== SMTP beállítások – ÁLLÍTSD SAJÁT ADATAIDRA =====
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';           
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['GMAIL_USER'];     
            $mail->Password   = $_ENV['GMAIL_PASS'];       
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            // =======================================================

            $mail->setFrom('your_email@gmail.com', 'Dog Walk App');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Fiok megerosites';

            $activationLink = sprintf(
                'http://localhost/WP/activate.php?code=%s',
                urlencode($code)
            );

            $mail->Body = "
                <h2>Üdv a Dog Walk oldalán!</h2>
                <p>Kérlek, erősítsd meg a fiókodat a lenti gombra kattintva:</p>
                <p><a href='{$activationLink}'>Fiók aktiválása</a></p>
                <hr>
                <small>Ha nem te regisztráltál, hagyd figyelmen kívül ezt az üzenetet.</small>
            ";

            $mail->send();
        } catch (Exception $e) {
            // E‑mail hiba esetén csak naplózunk, a regisztrációt nem töröljük
            error_log('PHPMailer error: ' . $mail->ErrorInfo);
        }
    }
}
