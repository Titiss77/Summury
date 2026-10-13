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
            cache()->save('reset_code_' . md5($email), $code, 900);

            // Chargement de la configuration via l'instance fraîche
            $emailConfig = config('Email');
            $emailService = \Config\Services::email();
            $emailService->initialize($emailConfig);
            
            // Assignation stricte de l'expéditeur
            $emailService->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
            $emailService->setTo($email);
            $emailService->setSubject('Code de réinitialisation de mot de passe');
            
            $message = "
            <div style='font-family: \"Inter\", Helvetica, Arial, sans-serif; background-color: #f3f5f8; padding: 40px 20px; color: #182131;'>
                <div style='max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e1e5ec;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <h2 style='color: #182131; margin: 0; font-size: 22px; font-weight: 800;'>CARDEX</h2>
                    </div>
                    
                    <p style='font-size: 15px; line-height: 1.6; color: #657084;'>Bonjour,</p>
                    <p style='font-size: 15px; line-height: 1.6; color: #657084;'>Vous avez demandé à réinitialiser votre mot de passe. Voici votre code de vérification :</p>
                    
                    <div style='text-align: center; margin: 35px 0;'>
                        <span style='display: inline-block; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #ffffff; background-color: #98733d; padding: 15px 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(152, 115, 61, 0.2);'>{$code}</span>
                    </div>
                    
                    <p style='font-size: 14px; text-align: center; color: #657084;'>Ce code est valide pendant <strong>15 minutes</strong>.</p>
                    
                    <hr style='border: none; border-top: 1px solid #e1e5ec; margin: 30px 0;'>
                    
                    <p style='font-size: 12px; text-align: center; color: #8791a1; margin: 0; line-height: 1.5;'>Si vous n'avez pas fait cette demande, vous pouvez ignorer cet e-mail en toute sécurité. Votre compte reste protégé.</p>
                </div>
            </div>
            ";
            
            $emailService->setMessage($message);

            if (!$emailService->send()) {
                log_message('error', 'Erreur envoi e-mail réinitialisation : ' . $emailService->printDebugger(['headers']));
                return redirect()->back()->with('error', "Une erreur est survenue lors de l'envoi de l'e-mail. Veuillez réessayer plus tard.");
            }
        }

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
            'password'         => 'required|min_length[8]',
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