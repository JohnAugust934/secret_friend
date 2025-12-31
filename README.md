# 🎁 Gerenciador de Amigo Secreto

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

Um sistema web moderno, seguro e fácil de usar para organizar eventos de **Amigo Secreto** (Secret Santa). Chega de papeizinhos! Crie grupos, convide amigos via link, defina orçamentos e deixe o sistema realizar o sorteio automaticamente.

---

## 🚀 Demo em Produção

O projeto está rodando ao vivo e pode ser acessado no link abaixo:

👉 **[sfriend.on3digital.com.br](https://sfriend.on3digital.com.br/)**

---

## ✨ Funcionalidades

O sistema foi desenvolvido para ser intuitivo e direto ao ponto:

-   **👤 Autenticação Completa:** Cadastro, login e recuperação de senha seguros.
-   **👥 Gerenciamento de Grupos:**
    -   Crie múltiplos grupos (ex: "Família", "Trabalho").
    -   Defina **data do evento**, **orçamento sugerido** e descrição.
    -   Apenas o dono do grupo (admin) pode editar ou excluir o grupo.
-   **🔗 Sistema de Convites:**
    -   Geração de links únicos com tokens de convite.
    -   Qualquer pessoa com o link pode entrar no grupo (após logar/cadastrar).
-   **📝 Lista de Desejos (Wishlist):**
    -   Cada participante pode escrever o que gostaria de ganhar.
    -   A lista é visível para quem te tirou.
    -   *Bloqueio de edição:* Após o sorteio, a lista não pode ser alterada para evitar confusões.
-   **🎲 Sorteio Automático:**
    -   Algoritmo inteligente que garante que ninguém tire a si mesmo.
    -   O sorteio é realizado com um clique pelo administrador.
    -   Resultado sigiloso: cada um vê apenas o seu par.
-   **🚫 Moderação:**
    -   O administrador pode remover membros antes do sorteio acontecer.

---

## 🛠️ Tecnologias Utilizadas

Este projeto utiliza a stack moderna do ecossistema PHP:

-   **Backend:** [Laravel Framework](https://laravel.com) (v12.x)
-   **Linguagem:** PHP 8.2+
-   **Frontend:** Blade Templates com [Tailwind CSS](https://tailwindcss.com) e Alpine.js (via Laravel Breeze)
-   **Banco de Dados:** MySQL / SQLite / PostgreSQL
-   **Build Tool:** Vite

---

## 💻 Instalação e Auto-Hospedagem (Self-Hosting)

Siga os passos abaixo para rodar o projeto localmente ou em seu servidor.

### Pré-requisitos

-   PHP 8.2 ou superior
-   Composer
-   Node.js e NPM
-   Banco de dados (MySQL ou SQLite)

### Passo a Passo

1.  **Clone o repositório:**
    ```bash
    git clone [https://github.com/seu-usuario/secret-friend.git](https://github.com/seu-usuario/secret-friend.git)
    cd secret-friend
    ```

2.  **Instale as dependências do PHP:**
    ```bash
    composer install
    ```

3.  **Configure o ambiente:**
    ```bash
    cp .env.example .env
    ```
    *Abra o arquivo `.env` e configure suas credenciais de banco de dados (`DB_DATABASE`, `DB_USERNAME`, etc).*

4.  **Gere a chave da aplicação:**
    ```bash
    php artisan key:generate
    ```

5.  **Execute as migrações (Criação das tabelas):**
    ```bash
    php artisan migrate
    ```

6.  **Instale e compile os assets (Frontend):**
    ```bash
    npm install
    npm run build
    ```

7.  **Inicie o servidor local:**
    ```bash
    php artisan serve
    ```
    Acesse `http://localhost:8000`.

---

## 🤝 Como Contribuir

Contribuições são sempre bem-vindas! Se você quiser adicionar uma nova funcionalidade ou corrigir um bug:

1.  Faça um **Fork** do projeto.
2.  Crie uma Branch para sua feature (`git checkout -b feature/MinhaNovaFeature`).
3.  Faça o Commit (`git commit -m 'Adiciona nova feature'`).
4.  Faça o Push (`git push origin feature/MinhaNovaFeature`).
5.  Abra um **Pull Request**.

---

## 📂 Estrutura Principal

Para quem deseja entender o código, os principais arquivos lógicos estão em:

-   `app/Http/Controllers/GroupController.php`: Lógica de criação, sorteio e gestão de membros.
-   `app/Models/Group.php`: Modelo do grupo e relações.
-   `app/Models/Pairing.php`: Modelo que armazena os pares sorteados (Santa -> Giftee).
-   `routes/web.php`: Definição de todas as rotas e proteções via middleware.

---

## 📄 Licença

Este projeto é open-source e licenciado sob a [MIT License](LICENSE).

---

<p align="center">
  Desenvolvido com ❤️ para unir amigos e famílias.
</p>