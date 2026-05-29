<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Versão enfileirada da notificação de verificação de e-mail.
 *
 * Por que esta classe existe?
 * A notificação padrão do Laravel (VerifyEmail) não implementa ShouldQueue,
 * o que significa que o SMTP é chamado de forma síncrona durante o registro
 * do usuário. Se o servidor de e-mail estiver indisponível, o usuário
 * recebe um erro 500 fatal.
 *
 * Esta classe herda toda a lógica de geração de URL assinada da VerifyEmail
 * original e adiciona apenas o comportamento de enfileiramento, enviando o
 * job para a fila 'emails' com alta prioridade.
 */
class VerifyEmailQueued extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Número de tentativas antes de mover para failed_jobs.
     */
    public int $tries = 3;

    /**
     * Tempo de espera (segundos) entre tentativas (backoff exponencial).
     *
     * @var array<int, int>
     */
    public array $backoff = [30, 60, 120];

    /**
     * Tempo máximo de execução do job em segundos.
     * Deve ser menor que o retry_after da fila (90s) para evitar jobs duplicados.
     */
    public int $timeout = 60;

    public function __construct()
    {
        // Direciona para a fila de e-mails, processada com prioridade máxima
        // pelo Supervisor (--queue=emails,default).
        $this->onQueue('emails');
    }

    /**
     * @var mixed
     */
    protected $user;

    /**
     * Override toMail to capture the notifiable instance.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $this->user = $notifiable;

        return parent::toMail($notifiable);
    }

    /**
     * Monta o e-mail usando a view customizada do projeto.
     *
     * Mantém o mesmo template visual que já estava configurado no
     * AppServiceProvider::boot() via VerifyEmail::toMailUsing().
     */
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifique seu E-mail — Amigo Secreto da Galera')
            ->view('emails.verify-email', [
                'url'  => $url,
                'name' => $this->user->name,
            ]);
    }
}
