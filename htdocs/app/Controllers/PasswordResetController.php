<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;

class PasswordResetController extends BaseController
{
    private const CODE_TTL = 900;
    private const VERIFIED_TTL = 600;
    private const MAX_ATTEMPTS = 5;
    private const MAX_SENDS_PER_HOUR = 3;

    public function forgotPasswordView()
    {
        return view('auth/forgot_password');
    }

    public function sendCode()
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Veuillez fournir une adresse e-mail valide.');
        }
        $emailHash = hash('sha256', $email);
        $ipHash = hash('sha256', (string) $this->request->getIPAddress());
        $sendKey = 'password_reset_send_' . $emailHash;
        $ipKey = 'password_reset_ip_' . $ipHash;
        $emailSends = (int) (cache()->get($sendKey) ?? 0);
        $ipSends = (int) (cache()->get($ipKey) ?? 0);

        if ($emailSends < self::MAX_SENDS_PER_HOUR && $ipSends < 10) {
            cache()->save($sendKey, $emailSends + 1, 3600);
            cache()->save($ipKey, $ipSends + 1, 3600);
            $user = auth()->getProvider()->findByCredentials(['email' => $email]);
            if ($user) {
                $code = (string) random_int(100000, 999999);
                cache()->save('password_reset_code_' . $emailHash, password_hash($code, PASSWORD_DEFAULT), self::CODE_TTL);
                cache()->save('password_reset_attempts_' . $emailHash, 0, self::CODE_TTL);
                cache()->delete('password_reset_verified_' . $emailHash);
                $emailConfig = config('Email');
                $emailService = \Config\Services::email();
                $emailService->initialize($emailConfig);
                $emailService->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
                $emailService->setTo($email);
                $emailService->setSubject('Code de réinitialisation de mot de passe');
                $siteName = esc((string) env('SITENAME', ''));
                $message = '<div style="font-family:Arial,sans-serif;padding:24px;color:#182131">'
                    . '<h2>' . $siteName . '</h2><p>Votre code de réinitialisation :</p>'
                    . '<p style="font-size:30px;font-weight:bold;letter-spacing:8px">' . esc($code) . '</p>'
                    . '<p>Ce code expire dans 15 minutes. Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.</p></div>';
                $emailService->setMessage($message);
                if (!$emailService->send()) {
                    log_message('error', 'Échec de l’envoi du code de réinitialisation.');
                    cache()->delete('password_reset_code_' . $emailHash);
                }
            }
        }
        session()->remove(['reset_verified', 'reset_token']);
        session()->set('reset_email', $email);
        return redirect()->to('forgot-password/verify')
            ->with('message', 'Si cette adresse correspond à un compte, un code de vérification a été envoyé.');
    }

    public function verifyCodeView()
    {
        if (!session()->has('reset_email')) return redirect()->to('forgot-password');
        return view('auth/verify_reset_code');
    }

    public function verifyCode()
    {
        $email = strtolower(trim((string) session('reset_email')));
        $code = trim((string) $this->request->getPost('code'));
        if ('' === $email || !preg_match('/^\d{6}$/', $code)) {
            return redirect()->to('forgot-password')->with('error', 'Session expirée ou code invalide.');
        }
        $emailHash = hash('sha256', $email);
        $codeKey = 'password_reset_code_' . $emailHash;
        $attemptKey = 'password_reset_attempts_' . $emailHash;
        $attempts = (int) (cache()->get($attemptKey) ?? 0);
        if ($attempts >= self::MAX_ATTEMPTS) {
            cache()->delete($codeKey);
            session()->remove(['reset_email', 'reset_verified', 'reset_token']);
            return redirect()->to('forgot-password')->with('error', 'Trop de tentatives. Demandez un nouveau code.');
        }
        $storedHash = cache()->get($codeKey);
        if (is_string($storedHash) && password_verify($code, $storedHash)) {
            $token = bin2hex(random_bytes(32));
            cache()->save('password_reset_verified_' . $emailHash, hash('sha256', $token), self::VERIFIED_TTL);
            session()->set(['reset_verified' => true, 'reset_token' => $token]);
            cache()->delete($attemptKey);
            return redirect()->to('forgot-password/reset')->with('message', 'Code validé. Choisissez votre nouveau mot de passe.');
        }
        cache()->save($attemptKey, $attempts + 1, self::CODE_TTL);
        if ($attempts + 1 >= self::MAX_ATTEMPTS) {
            cache()->delete($codeKey);
            session()->remove(['reset_email', 'reset_verified', 'reset_token']);
            return redirect()->to('forgot-password')->with('error', 'Trop de tentatives. Demandez un nouveau code.');
        }
        return redirect()->back()->with('error', 'Code invalide ou expiré.');
    }

    public function resetPasswordView()
    {
        if (!$this->hasValidResetAuthorization()) return redirect()->to('forgot-password');
        return view('auth/reset_password');
    }

    public function updatePassword()
    {
        if (!$this->hasValidResetAuthorization()) return redirect()->to('forgot-password');
        $rules = ['password' => 'required|min_length[12]', 'password_confirm' => 'required|matches[password]'];
        if (!$this->validate($rules)) return redirect()->back()->with('errors', $this->validator->getErrors());

        $email = strtolower(trim((string) session('reset_email')));
        $emailHash = hash('sha256', $email);
        $users = auth()->getProvider();
        $user = $users->findByCredentials(['email' => $email]);
        if (!$user) {
            session()->remove(['reset_email', 'reset_verified', 'reset_token']);
            return redirect()->to('forgot-password')->with('error', 'La réinitialisation a expiré. Recommencez.');
        }
        $user->fill(['password' => $this->request->getPost('password')]);
        if (!$users->save($user)) return redirect()->back()->with('error', 'Impossible de modifier le mot de passe. Réessayez.');

        (new AuditLogModel())->logAction('Réinitialisation Mot de passe', 'Un mot de passe a été réinitialisé via le code e-mail.');
        cache()->delete('password_reset_code_' . $emailHash);
        cache()->delete('password_reset_attempts_' . $emailHash);
        cache()->delete('password_reset_verified_' . $emailHash);
        session()->remove(['reset_email', 'reset_verified', 'reset_token']);
        return redirect()->to('z')->with('message', 'Votre mot de passe a été modifié. Vous pouvez maintenant vous connecter.');
    }

    private function hasValidResetAuthorization(): bool
    {
        $email = strtolower(trim((string) session('reset_email')));
        $token = (string) session('reset_token');
        if ('' === $email || '' === $token || !session()->get('reset_verified')) return false;
        $storedHash = cache()->get('password_reset_verified_' . hash('sha256', $email));
        return is_string($storedHash) && hash_equals($storedHash, hash('sha256', $token));
    }
}
