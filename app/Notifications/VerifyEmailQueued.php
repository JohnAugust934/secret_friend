<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;

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
 *
 * Nota sobre $notifiable: o Laravel passa o modelo do usuário diretamente
 * para toMail() durante a execução do job na fila. SerializesModels garante
 * que modelos Eloquent sejam serializados pelo ID e recarregados do banco
 * ao desserializar, evitando dados desatualizados ou objetos corrompidos.
 */
class VerifyEmailQueued extends VerifyEmail implements ShouldQueue
{
    use Queueable, SerializesModels;

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

    /**
     * Referência ao notifiable capturada em toMail() para uso em buildMailMessage().
     * Definida em runtime — não é serializada junto com o job.
     *
     * @var mixed
     */
    protected $notifiable;

    public function __construct()
    {
        // Direciona para a fila de e-mails, processada com prioridade máxima
        // pelo Supervisor (--queue=emails,default).
        $this->onQueue('emails');
    }

    /**
     * Override toMail para capturar o notifiable antes de chamar buildMailMessage().
     *
     * O fluxo do Laravel é: toMail() → buildMailMessage(). Capturamos o
     * notifiable aqui para disponibilizá-lo em buildMailMessage() sem
     * necessidade de serialização de estado entre os dois métodos.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $this->notifiable = $notifiable;

        return parent::toMail($notifiable);
    }

    /**
     * Monta o e-mail usando a view customizada do projeto.
     *
     * Usa $this->notifiable capturado em toMail(). Se por algum motivo
     * $notifiable for null (jamais deveria ocorrer no fluxo normal),
     * usa 'Usuário' como fallback seguro.
     */
    protected function buildMailMessage($url): MailMessage
    {
        $name = $this->notifiable->name ?? 'Usuário';

        return (new MailMessage)
            ->subject('Verifique seu E-mail — Amigo Secreto da Galera')
            ->view('emails.verify-email', [
                'url'  => $url,
                'name' => $name,
            ]);
    }
}
