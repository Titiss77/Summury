<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;

class PasswordResetController extends BaseController
{
    public function forgotPasswordView()
    {
        return view('auth/forgot_password');
    }

    public function sendCode()
    {
        $email = $this->request->getPost('email');
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Veuillez fournir une adresse e-mail valide.');
        }

        $users = auth()->getProvider();
        $user = $users->findByCredentials(['email' => $email]);

        if ($user) {
            $code = (string) random_int(100000, 999999);
            // On stocke le code dans le cache de CI4 (durée : 15 minutes)
            cache()->save('reset_code_' . md5($email), $code, 900);

            $emailService = \Config\Services::email();
            $emailService->setTo($email);
            $emailService->setSubject('Code de réinitialisation de mot de passe');
            
            $message = "Bonjour,<br><br>";
            $message .= "Vous avez demandé la réinitialisation de votre mot de passe.<br>";
            $message .= "Voici votre code de vérification à 6 chiffres : <strong style='font-size:1.5rem;'>{$code}</strong><br><br>";
            $message .= "Ce code est valide pendant 15 minutes.<br>";
            $message .= "Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail en toute sécurité.";
            
            $emailService->setMessage($message);
            $emailService->setMailType('html');

            if (!$emailService->send()) {
                log_message('error', 'Erreur envoi e-mail réinitialisation : ' . $emailService->printDebugger(['headers']));
                return redirect()->back()->with('error', "Une erreur est survenue lors de l'envoi de l'e-mail. Veuillez réessayer plus tard.");
            }
        }

        // Par sécurité anti-énumération de compte, on affiche toujours un succès d'envoi.
        session()->set('reset_email', $email);
        return redirect()->to('forgot-password/verify')->with('message', "Si l'adresse e-mail correspond à un compte, un code à 6 chiffres vous a été envoyé.");
    }

    public function verifyCodeView()
    {
        if (!session()->has('reset_email')) {
            return redirect()->to('forgot-password');
        }
        return view('auth/verify_reset_code');
    }

    public function verifyCode()
    {
        $email = session('reset_email');
        $code = $this->request->getPost('code');

        if (!$email || !$code) {
            return redirect()->to('forgot-password')->with('error', 'Session expirée ou code manquant.');
        }

        $cachedCode = cache('reset_code_' . md5($email));

        if ($cachedCode && $cachedCode === $code) {
            session()->set('reset_verified', true);
            return redirect()->to('forgot-password/reset')->with('message', 'Code validé. Veuillez choisir votre nouveau mot de passe.');
        }

        return redirect()->back()->with('error', 'Code invalide ou expiré.');
    }

    public function resetPasswordView()
    {
        if (!session()->has('reset_email') || !session()->get('reset_verified')) {
            return redirect()->to('forgot-password');
        }
        return view('auth/reset_password');
    }

    public function updatePassword()
    {
        if (!session()->has('reset_email') || !session()->get('reset_verified')) {
            return redirect()->to('forgot-password');
        }

        $rules = [
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $email = session('reset_email');
        $users = auth()->getProvider();
        $user = $users->findByCredentials(['email' => $email]);

        if ($user) {
            $user->fill(['password' => $this->request->getPost('password')]);
            $users->save($user);

            (new AuditLogModel())->logAction('Réinitialisation Mot de passe', "L'utilisateur ID {$user->id} a réinitialisé son mot de passe via un code de secours e-mail.");
        }

        // Nettoyage
        cache()->delete('reset_code_' . md5($email));
        session()->remove(['reset_email', 'reset_verified']);

        return redirect()->to('z')->with('message', 'Votre mot de passe a été modifié avec succès. Vous pouvez maintenant vous connecter.');
    }
}