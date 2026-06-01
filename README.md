# 🎁 Gerenciador de Amigo Secreto

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Playwright](https://img.shields.io/badge/Playwright-E2E-2EAD33?style=for-the-badge&logo=playwright&logoColor=white)
![k6](https://img.shields.io/badge/k6-Load_Testing-7C6BFF?style=for-the-badge&logo=k6&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

Um sistema web completo, moderno, seguro e extremamente prático para organizar eventos de **Amigo Secreto** (Secret Santa). Desenvolvido para eliminar o uso de papéis físicos, o sistema oferece controle total para o administrador, sincronização em tempo real dos participantes, restrições inteligentes e ferramentas avançadas de monitoramento operacional e resiliência (Pronto para Produção).

---

## 🚀 Demonstração em Produção

O projeto está configurado e rodando ao vivo no endereço abaixo:

👉 **[sfriend.on3digital.com.br](https://sfriend.on3digital.com.br/)**

---

## ✨ Funcionalidades Principais

O sistema foi desenhado visando a melhor experiência do usuário e alta confiabilidade:

### 👤 Autenticação e Segurança
*   **Acesso Seguro:** Fluxo completo de cadastro, login e recuperação de senha segura (Laravel Breeze).
*   **Proteção de Rotas:** Todos os recursos sensíveis e dados de sorteio são protegidos por middlewares de autorização (Gates/Policies).
*   **Segurança Anti-Spam:** Rate limiters integrados em ações críticas, como criação de grupos, sorteios e convites.

### 👥 Gerenciamento de Grupos
*   **Customização Completa:** Criação de grupos definindo nome, data do evento, orçamento sugerido, limite de orçamento, local e descrição.
*   **Painel Administrativo:** Apenas o criador do grupo (admin) pode editar informações, excluir o grupo ou realizar o sorteio.
*   **Links de Convite Dinâmicos:** Geração de links únicos baseados em tokens seguros. Se o sorteio já tiver ocorrido, o link é invalidado automaticamente.
*   **Moderação Ativa:** O administrador pode remover membros indesejados antes da realização do sorteio.

### ⛔ Restrições de Sorteio (Exclusões)
*   **Evite Conflitos:** Permite cadastrar restrições para impedir que participantes específicos tirem uns aos outros (ex: casais, pais e filhos, ou membros da mesma casa).
*   **Prevenção de Deadlocks:** O algoritmo de sorteio valida as restrições para garantir que o sorteio ocorra sem travamentos, exigindo no mínimo 3 participantes no grupo.

### 🎲 Algoritmo de Sorteio Inteligente
*   **Segredo Absoluto:** Cada participante visualiza apenas o seu par sorteado diretamente no seu painel.
*   **Não Repetição:** Em caso de re-sorteio (redraw), o sistema registra o histórico (`draw_round`) e minimiza a repetição das combinações anteriores.
*   **Trilha de Auditoria:** Gravação em banco (`draw_audits`) do autor do sorteio, rodada, endereço de IP e data/hora.

### 📝 Lista de Desejos (Wishlist)
*   **Ideias de Presentes:** Cada participante pode editar sua lista de desejos a qualquer momento antes do sorteio.
*   **Visualização Direta:** O padrinho (quem tirou você) visualiza suas sugestões de presentes em tempo real.
*   **Bloqueio de Edição:** Após o sorteio ser realizado, a lista de desejos é bloqueada para edição para evitar mudanças repentinas e confusão.

### ⚡ Sincronização em Tempo Real (SSE)
*   **Server-Sent Events:** Tela de visualização do grupo atualizada de forma assíncrona e em tempo real quando novos participantes entram, sem necessidade de atualizar a página manualmente ou fazer polling pesado.

---

## 🛠️ Tecnologias Utilizadas

Este projeto utiliza a stack moderna do ecossistema PHP e Node:

*   **Backend:** [Laravel Framework](https://laravel.com) (v12.x)
*   **Linguagem:** PHP 8.2+ ou superior
*   **Frontend:** Blade Templates, [Alpine.js](https://alpinejs.dev/) para reatividade e [Tailwind CSS](https://tailwindcss.com/)
*   **Banco de Dados:** MySQL / MariaDB / PostgreSQL / SQLite
*   **Ferramenta de Build:** Vite
*   **Serviço de Fila (Queues):** Processamento assíncrono para envio de e-mails em segundo plano

---

## 📦 Estrutura de Arquivos Operacionais

Para quem deseja entender a lógica do código, os principais pontos são:

*   `app/Http/Controllers/GroupController.php`: Processamento de grupos, exclusões, moderação e SSE Stream.
*   `app/Services/DrawService.php`: Algoritmo de sorteio inteligente aplicando as exclusões e prevenindo combinações repetidas.
*   `app/Models/Group.php` & `app/Models/Pairing.php`: Modelos de negócio do grupo, histórico de rodadas e pareamento.
*   `routes/web.php` & `routes/console.php`: Definição de rotas da aplicação, endpoints de monitoramento e agendamentos Artisan.

---

## 💻 Instalação e Desenvolvimento Local

Siga o guia passo a passo abaixo para configurar o ambiente de desenvolvimento local.

### Pré-requisitos
*   PHP 8.2+
*   Composer
*   Node.js & NPM
*   Banco de dados (MySQL ou SQLite)

### Passos de Configuração

1.  **Clonar o repositório:**
    ```bash
    git clone https://github.com/JohnAugust934/secret_friend.git
    cd secret_friend
    ```

2.  **Instalar as dependências do PHP:**
    ```bash
    composer install
    ```

3.  **Configurar o arquivo de ambiente:**
    ```bash
    cp .env.example .env
    ```
    *Abra o arquivo `.env` e configure suas credenciais de banco de dados e SMTP para disparo de e-mails.*

4.  **Gerar chave da aplicação:**
    ```bash
    php artisan key:generate
    ```

5.  **Executar migrações do banco de dados:**
    ```bash
    php artisan migrate
    ```

6.  **Instalar e compilar pacotes do frontend:**
    ```bash
    npm install
    npm run dev
    ```

7.  **Iniciar servidor local:**
    ```bash
    php artisan serve
    ```
    Acesse `http://localhost:8000`.

### 👥 Usuário de Teste Local (Seeder)
Para testar a aplicação rapidamente com dados prontos sem precisar criar uma conta do zero:
*   **Seeder:** `database/seeders/TestUserSeeder.php`
*   **Comando de execução:**
    ```bash
    php artisan db:seed --class=TestUserSeeder
    ```
*   **Credenciais de Acesso:**
    *   **E-mail:** `teste@amigosecreto.local`
    *   **Senha:** `Teste@123456`

---

## 📈 Operação e Resiliência em Produção (Go-Live)

O sistema possui infraestrutura completa de monitoramento, automações de fila e políticas de resiliência.

### 🌐 Endpoints de Monitoramento e Telemetria
*   **`/up`**: Endpoint de integridade rápida nativo do Laravel.
*   **`/healthz`**: Endpoint avançado de integridade da infraestrutura. Retorna dados estruturados de conectividade de Banco de Dados, Cache e quantidade de tarefas pendentes ou falhas na fila.
    > [!IMPORTANT]
    > Segurança: Quando a variável `OPS_HEALTHCHECK_SECRET` é definida no `.env`, o `/healthz` passa a exigir o cabeçalho `X-Health-Token` correspondente para evitar que varreduras externas obtenham detalhes da topologia da infraestrutura.
*   **`/ops/status`**: Painel administrativo de status do sistema.
    *   Acesso restrito apenas a administradores cujos e-mails estejam listados em `OPS_STATUS_ALLOWED_EMAILS` no `.env`.
*   **`/telemetry/frontend`**: Endpoint de recepção de telemetria. Logs de erros de Javascript do lado do cliente e métricas de navegação são disparados de volta ao servidor para gravação automática nos logs operacionais.

### ⏰ Automatização e Scheduler (Cron Jobs)
Para garantir o envio de e-mails assíncronos e a saúde contínua, configure apenas **um comando cron** em seu servidor de hospedagem (ex: VPS ou Hostinger):

```cron
* * * * * /opt/alt/php84/usr/bin/php /caminho-da-sua-aplicacao/artisan schedule:run >> /dev/null 2>&1
```

O Scheduler integrado gerencia todas as rotinas internas:
1.  **Trabalho da Fila (`queue:work`)**: Executado a cada minuto para liberar o fluxo de e-mails assíncronos sem sobrecarga de memória (`--stop-when-empty --tries=3`).
2.  **Verificador de Saúde (`ops:health-check`)**: Roda a cada 5 minutos testando a integridade interna de todos os serviços.
3.  **Monitor de Falhas de E-mail (`ops:check-failed-mails`)**: Roda a cada 5 minutos alertando o administrador caso e-mails fiquem presos na fila de envio.
4.  **Backup Diário do Banco (`ops:backup-db`)**: Realizado todos os dias às 02:30 da manhã, gerando um dump compactado e validando a integridade básica com retenção configurável (`OPS_BACKUP_RETENTION_DAYS`).

### 📬 Alertas Operacionais
Em caso de falhas em filas ou indisponibilidade detectada, o sistema dispara alertas estruturados:
*   Se as credenciais `TELEGRAM_BOT_TOKEN` e `TELEGRAM_CHAT_ID` estiverem definidas no `.env`, os alertas serão enviados **imediatamente via bot do Telegram**.
*   Caso o bot do Telegram não esteja configurado, o sistema envia os alertas operacionais para o e-mail cadastrado em `OPS_ALERT_EMAIL`.

### 🧪 Diagnóstico de E-mail SMTP
Caso e-mails parem de chegar aos usuários, utilize o utilitário integrado para identificar rapidamente se o problema está na conexão SMTP ou na fila de processamento:
```bash
php artisan ops:diagnose-email --to=seu-email@dominio.com
```

---

## 🛟 Recuperação de Desastres (Disaster Recovery)

Se o banco de dados for corrompido ou houver falha crítica, você pode restaurar o último backup imediatamente.

### Procedimento de Restore
1.  **Coloque a aplicação em manutenção:**
    ```bash
    php artisan down --render="errors::503"
    ```
2.  **Rode o comando de restauração:**
    ```bash
    php artisan ops:restore-db /caminho/do/seu/backup.sql --force
    ```
    > [!TIP]
    > O comando `ops:restore-db` realiza automaticamente um backup de segurança temporário antes do restore (pre-backup preventivo), a menos que o parâmetro `--skip-pre-backup` seja explicitamente passado.
3.  **Limpe o cache operacional e valide a restauração:**
    ```bash
    php artisan optimize:clear && php artisan optimize
    php artisan ops:readiness
    ```
4.  **Reative a aplicação:**
    ```bash
    php artisan up
    ```

---

## 🧪 Testes de Qualidade, E2E e Concorrência

O sistema possui uma sólida esteira de testes automatizados para garantir a qualidade de ponta a ponta:

*   **Padrões de Código (Linting):**
    ```bash
    vendor/bin/pint --test
    ```
*   **Testes Unitários e Funcionais (PHPUnit):**
    ```bash
    php artisan test
    ```
*   **Testes de Ponta a Ponta (Playwright E2E):**
    ```bash
    npm run e2e
    npm run e2e:headed
    ```
*   **Testes de Carga e Concorrência (k6):**
    *   Teste de fumaça rápida (smoke test): `k6 run scripts/ops/k6-smoke.js`
    *   Teste de gargalo de concorrência focado em convites simultâneos e sorteios:
        ```bash
        k6 run scripts/ops/k6-concurrency.js -e BASE_URL=https://SEU_DOMINIO -e GROUP_ID=ID -e INVITE_TOKEN=TOKEN -e COOKIE="laravel_session=..."
        ```

---

## 📄 Licença

Este projeto é um software open-source licenciado sob a [MIT License](LICENSE).

---
<p align="center">
  Desenvolvido com ❤️ para aproximar amigos e famílias.
</p>
