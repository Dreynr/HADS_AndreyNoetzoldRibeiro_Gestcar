# GestCar

Sistema web de gestão e agendamento para estéticas automotivas.

**Ambiente de homologação:** [gestcar.prowebprofile.com](https://gestcar.prowebprofile.com) — publicado para testes; a entrada em operação na estética está prevista no cronograma.

Trabalho de Conclusão de Curso — Análise e Desenvolvimento de Sistemas, Universidade de Passo Fundo (UPF), 2026.
Autor: **Andrey Ribeiro**

---

## Sobre o projeto

O GestCar nasceu de um problema real. Sou proprietário de uma estética automotiva, e toda a operação era manual: os agendamentos eram combinados por WhatsApp ou telefone e anotados à mão, os serviços realizados iam para o papel ou para planilhas, e o fechamento do mês era feito somando tudo manualmente. O resultado eram horários esquecidos, conflitos de agenda, clientes que não apareciam e histórico perdido.

O sistema tem duas áreas, acessadas pelo navegador do celular ou do computador, sem precisar instalar nada:

- **Área do cliente:** cadastro, veículos, agendamento online e histórico de atendimentos.
- **Área administrativa:** agenda, catálogo de serviços, clientes e acompanhamento comercial.

---

## Funcionalidades

### Implementadas

| Funcionalidade | Descrição |
|---|---|
| Autenticação com dois perfis | Cliente e administrador, cada um com acesso apenas à sua área. Recuperação de senha por código de 6 dígitos enviado por e-mail |
| Veículos | O cliente cadastra e gerencia os próprios carros |
| Catálogo de serviços | Serviços principais e extras, com preço e duração |
| Agendamento por período | O cliente escolhe data e período disponível, com controle de capacidade e adição de serviços extras |
| Agenda administrativa | Visão por dia, semana e mês, com filtros e mudança de status |
| Notificações por e-mail | Boas-vindas, aviso de novo agendamento, mudança de status e lembretes automáticos 1 dia e 1 hora antes |
| Acompanhamento de clientes (CRM) | Segmentação automática em novo, recorrente, VIP e inativo, com notas e lembretes de contato |

### Em desenvolvimento

| Requisito | Situação |
|---|---|
| Segurança, LGPD e anonimização na exclusão de conta (RF01, RNF09) | Em andamento |
| Notificações pelo WhatsApp, pela Z-API, com o e-mail de reserva (RF10) | Pendente |
| Registro de lavagens com o valor cobrado (RF06) | Pendente |
| Fechamento mensal com faturamento e ticket médio (RF08) | Pendente — depende do RF06 |
| Painel de clientes inativos com ações de retorno (RF09) | Parcial |
| Controle de estoque (RF07) | Pendente |

---

## A regra de agendamento

O agendamento não é por horário exato, e sim por **período** — porque é assim que a estética funciona. O tempo real de uma lavagem varia conforme o estado do carro, e marcar horários exatos criaria atrasos em cadeia.

- O atendimento é organizado em três períodos: **manhã**, **tarde** e **noite**.
- Em dias de semana apenas o período da **noite** fica disponível; nos fins de semana, os três.
- Cada período aceita um número limitado de agendamentos, configurável no banco (`settings.max_simple_per_period`).
- Serviços mais demorados ocupam o **período inteiro** e bloqueiam os demais agendamentos daquele período.
- Agendamentos cancelados liberam a vaga.

A regra está concentrada no model `App\Models\Appointment`.

---

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Linguagem | PHP 8.2 |
| Framework | Laravel 12 (arquitetura MVC) |
| Autenticação | Laravel Breeze |
| Banco de dados | MariaDB 10.4 |
| Interface | Blade, Tailwind CSS e Alpine.js |
| Build de assets | Vite |
| Hospedagem | Hostinger, com SMTP para os e-mails e cron para os lembretes |
| WhatsApp | Z-API, com número dedicado (em integração) |

Ferramentas de desenvolvimento: VS Code, com GitHub Copilot e Claude Code como assistentes de código, e drawDB para a modelagem do banco.

---

## Estrutura

```
app/
├── Console/Commands/     rotina automática de lembretes
├── Http/
│   ├── Controllers/
│   │   ├── Admin/        área administrativa
│   │   └── Client/       área do cliente
│   ├── Middleware/       controle de acesso por perfil (EnsureRole)
│   └── Requests/         validação dos formulários
├── Mail/                 os cinco tipos de e-mail
└── Models/               regras de negócio e acesso ao banco
database/migrations/      estrutura do banco, em ordem cronológica
resources/views/          telas em Blade
routes/                   rotas web e tarefas agendadas
docs/                     documentação do projeto
```

---

## Como executar localmente

**Requisitos:** PHP 8.2+, Composer, Node.js 20.19+ (exigência do Vite 7) e MariaDB ou MySQL.

```bash
git clone <url-do-repositorio>
cd gestcar

composer install
npm install

cp .env.example .env        # no Windows: copy .env.example .env
php artisan key:generate
```

No arquivo `.env`, configure o banco de dados:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestcar
DB_USERNAME=root
DB_PASSWORD=
```

Crie um banco vazio chamado `gestcar` e rode:

```bash
php artisan migrate --seed   # cria as tabelas, os serviços de exemplo e as configurações
npm run dev                  # compila a interface (deixe rodando)
php artisan serve            # em outro terminal — acesse http://localhost:8000
```

Para testar os lembretes automáticos localmente, rode também `php artisan schedule:work`.

Em desenvolvimento, os e-mails não são enviados de verdade: com `MAIL_MAILER=log`, eles são gravados em `storage/logs/laravel.log`.

### Contas de teste

O seeder cria duas contas **apenas para o ambiente local**:

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@gestcar.com | password |
| Cliente | cliente@teste.com | password |

> Essas credenciais existem só para desenvolvimento. O ambiente publicado deve usar contas e senhas próprias.

---

## Documentação

A estrutura do banco de dados está em [docs/modelo_banco.sql](docs/modelo_banco.sql), incluindo as tabelas previstas.

O Documento de Visão do Produto (DVP) — com os requisitos funcionais e não funcionais, os diagramas UML, as histórias de usuário e o cronograma — é entregue separadamente à disciplina.
