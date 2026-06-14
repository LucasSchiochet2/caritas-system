# Caritas System

Sistema em Laravel para administração de paróquias, usuários administrativos, clientes e itens do bazar. O projeto expõe uma API autenticada com Laravel Sanctum, painel administrativo com Backpack e documentação OpenAPI.

## Requisitos

- PHP 8.3 ou superior
- Composer
- Node.js e npm
- SQLite habilitado no PHP, ou outro banco configurado no `.env`

## Instalação

Instale as dependências do PHP e do JavaScript:

```bash
composer install
npm install
```

Crie o arquivo de ambiente:

```bash
cp .env.example .env
```

No Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

Rode as migrations e os seeders:

```bash
php artisan migrate --seed
```

O seeder cria um usuário administrador para desenvolvimento:

- Email: `test@example.com`
- Senha: `password`

## Rodando o projeto


```bash
npm run dev
```

## API

A base da API é:

```text
http://127.0.0.1:8000/api
```

Login como administrador da diocese:

```http
POST /api/diocese/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "password"
}
```

Use o `access_token` retornado como Bearer Token nos endpoints protegidos.

Documentacao do estoque paroquial e saida de cestas:

```text
/documentacao-estoque
```


## Comandos úteis

Recriar o banco do zero com dados iniciais:

```bash
php artisan migrate:fresh --seed
```

Limpar caches da aplicação:

```bash
php artisan optimize:clear
```

Atualizar dependências após um pull:

```bash
composer install
npm install
php artisan migrate
```

## Configuração de banco

Por padrão, o `.env.example` usa SQLite:

```env
DB_CONNECTION=sqlite
```

Para usar MySQL, PostgreSQL ou outro banco suportado pelo Laravel, ajuste as variáveis `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no `.env`, depois rode:

```bash
php artisan migrate --seed
```
