<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class EmailSetting extends Authenticatable
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'smtp_settings';


    protected $guarded = ['id'];
    protected $appends = ['set_smtp_message'];

    public function verifySmtp()
    {
        if ($this->mail_driver !== 'smtp') {
            return [
                'success' => true,
                'message' => __('messages.smtpSuccess'),
            ];
        }

        try {
            $tls = $this->mail_encryption === 'ssl';
            $transport = new EsmtpTransport($this->mail_host, (int) $this->mail_port, $tls);
            $transport->setUsername($this->mail_username);
            $transport->setPassword($this->mail_password);
            $transport->start();

            if ($this->verified == 0) {
                $this->verified = 1;
                $this->save();
            }

            return [
                'success' => true,
                'message' => __('messages.smtpSuccess'),
            ];
        } catch (TransportException|\Exception $e) {
            $this->verified = 0;
            $this->save();

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getSetSmtpMessageAttribute(){
        if ($this->verified === 0 && $this->mail_driver == 'smtp') {
            return ' <div class="alert alert-danger">
                    '.__('messages.smtpNotSet').'
                    <a href="'.route('admin.smtp-settings.index').'" class="btn btn-info btn-small">Visit SMTP Settings <i
                                class="fa fa-arrow-right"></i></a>
                </div>';
        }
        return null;
    }
}
