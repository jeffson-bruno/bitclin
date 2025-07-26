<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# BitClin - Sistema de Gestão de Clínicas Médicas
**Versão:** v1.0  
**Status:** Estável (Versão de Entrada)

---

## 📝 Sumário
- [Descrição Geral](#descrição-geral)
- [Arquitetura do Sistema](#arquitetura-do-sistema)
- [Funcionalidades Atuais](#funcionalidades-atuais)
- [Painéis e Permissões](#painéis-e-permissões)
- [Painel do Médico - Funcionalidades Pendentes](#painel-do-médico---funcionalidades-pendentes)
- [Instalação Local](#instalação-local)
- [Licença](#licença)

---

## 📌 Descrição Geral

O **BitClin** é um sistema de gestão clínica desenvolvido com foco em clínicas de pequeno a médio porte. Esta documentação cobre a **versão de entrada (v1.0)**, com funcionalidades essenciais para operação clínica, incluindo recepção, agendamento, financeiro, e prontuário médico.

---

## 🧱 Arquitetura do Sistema

- **Frontend:** Vue 3 + Inertia.js + TailwindCSS
- **Backend:** Laravel 10+ (PHP 8.1+)
- **Banco de Dados:** MySQL
- **Autenticação:** Laravel Breeze (usando roles via middleware)
- **Componentização:** Separação de responsabilidades por perfil (`admin`, `receptionist`, `doctor`)
- **PDF:** Geração de documentos com DomPDF

### Estrutura de Pastas Relevantes

- `resources/js/Pages/Admin/` → Páginas administrativas (Exames, Consultas, Dashboard)
- `resources/js/Pages/Recepcao/` → Páginas da recepção
- `resources/js/Pages/Medico/` → Páginas do painel médico
- `resources/js/Components/` → Componentes reutilizáveis (Toast, ModalSenha, etc)
- `app/Http/Controllers/Admin/` → Controllers administrativos
- `app/Http/Controllers/Recepcao/` → Controllers da recepção
- `app/Http/Controllers/Medico/` → Controllers médicos
- `app/Models/` → Models de domínio (Paciente, Exame, AgendaMedica etc)

---

## ✅ Funcionalidades Atuais

### Painel do Administrador
- Cadastro de usuários (com roles)
- Cadastro de médicos com especialidade
- Cadastro de exames (dias disponíveis e turnos)
- Cadastro de agenda médica (datas, horários e valor da consulta)
- Dashboard com gráficos de faturamento e despesas
- Relatórios financeiros diários, semanais, mensais e anuais
- Painel de controle de despesas
- Visualização de pacientes agendados para consultas e exames

### Painel da Recepção
- Cadastro de pacientes (consulta ou exame)
- Busca automática de valor da consulta e exame
- Geração de ficha em PDF (com informações financeiras e médicas)
- Geração de senha de atendimento
- Dashboard com calendário de atendimentos e médicos
- Listagem de agendamentos realizados com ações
- Modal de reagendamento de pacientes

### Painel do Médico
- Visualização dos pacientes agendados para o dia atual
- Chamada de senha (com Toast de confirmação)
- **[em desenvolvimento]** Receita médica (PDF)
- **[em desenvolvimento]** Atestado médico (PDF)
- **[em desenvolvimento]** Solicitação de exames (PDF)
- **[em desenvolvimento]** Criação e armazenamento de prontuário

---

## 🔐 Painéis e Permissões

| Perfil         | Acesso                                                        |
|----------------|---------------------------------------------------------------|
| **Admin**      | Todos os cadastros, agenda médica, financeiro e relatórios    |
| **Recepcionista** | Agendamentos, cadastro de pacientes, consulta de horários |
| **Médico**      | Visualização de agendamentos e ações médicas por paciente    |

---

## 📌 Painel do Médico - Funcionalidades Pendentes (v1.1)

As próximas features que serão implementadas:

1. **Limite de 3 chamadas por paciente** com histórico da chamada.
2. **Emissão de Receita Médica** com layout personalizado.
3. **Emissão de Atestado Médico** com período e justificativa.
4. **Solicitação de Exames** com lista de exames padrão e adicionais.
5. **Criação de Prontuário Médico** com campos:
   - Pressão Arterial
   - Anotações médicas
   - Histórico clínico
   - Documentos emitidos

---

## 💻 Instalação Local

### Pré-requisitos
- PHP 8.1+
- Composer
- Node.js + NPM
- MySQL

### Passos

```bash
# 1. Clone o repositório
git clone https://github.com/seu-usuario/bitclin.git
cd bitclin

# 2. Instale dependências PHP
composer install

# 3. Instale dependências JS
npm install && npm run dev

# 4. Copie o arquivo de ambiente
cp .env.example .env

# 5. Gere a chave da aplicação
php artisan key:generate

# 6. Configure o banco de dados no .env e rode as migrations
php artisan migrate --seed

# 7. Rode o servidor
php artisan serve
```

> Acesse o sistema em `http://localhost:8000`

Credenciais padrão podem ser geradas no seeder ou via tinker:
```bash
php artisan tinker
>>> \App\Models\User::create([...]);
```

---

## ⚠️ Licença

Projeto desenvolvido por Jeffson Bruno - todos os direitos reservados.  
Versão de entrada do sistema BitClin - 2025.

---



