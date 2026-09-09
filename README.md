<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Atividade - Middleware Laravel

## Descrição

O controller aciona uma middleware que bloqueia o acesso e envia a seguinte mensagem na view:

> **Você não tem permissão para acessar este site.**
> **Favor entrar em contato com o administrador.**

## Estrutura do Projeto

```
middleware/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── HomeController.php        # Controller acionado pela rota
│   │   └── Middleware/
│   │       └── CheckPermission.php       # Middleware que bloqueia o acesso
│   └── ...
├── bootstrap/
│   └── app.php                           # Registro do alias da middleware
├── resources/
│   └── views/
│       ├── home.blade.php                # View principal (sem permissão)
│       └── acesso-negado.blade.php       # View com a mensagem de bloqueio
└── routes/
    └── web.php                           # Rota com middleware aplicada
```

## Algoritmo

### 1. Rota (`routes/web.php`)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [HomeController::class, 'index'])
    ->middleware('permission');
```

A rota `/home` aciona o `HomeController@index` e aplica a middleware **permission**.

### 2. Controller (`app/Http/Controllers/HomeController.php`)

```php
<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }
}
```

O controller retorna a view `home`, porém a middleware intercepta a requisição **antes** de chegar no controller.

### 3. Middleware (`app/Http/Middleware/CheckPermission.php`)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next)
    {
        $permissao = false;

        if (!$permissao) {
            return response()->view('acesso-negado', [], 403);
        }

        return $next($request);
    }
}
```

Como `$permissao` é `false`, a requisição é interceptada e a view `acesso-negado` é retornada com o status HTTP **403 Forbidden**.

### 4. Registro da middleware (`bootstrap/app.php`)

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'permission' => \App\Http\Middleware\CheckPermission::class,
    ]);
})
```

### 5. View com a mensagem (`resources/views/acesso-negado.blade.php`)

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acesso Negado</title>
</head>
<body>
    <h1>Acesso Negado</h1>
    <p>Você não tem permissão para acessar este site.</p>
    <p>Favor entrar em contato com o administrador.</p>
</body>
</html>
```

## Execuções da Middleware

### Como executar

```bash
php artisan serve
```

### Execução 1 - Acesso à rota `/` (sem middleware)

```
GET / → StatusCode: 200
```

A rota raiz não possui middleware e o acesso é liberado (`view('welcome')`).

### Execução 2 - Acesso à rota `/home` (com middleware)

```
GET /home → StatusCode: 403
```

A middleware `CheckPermission` intercepta a requisição e retorna a view de acesso negado com a mensagem:

```
<h1>Acesso Negado</h1>
<p>Você não tem permissão para acessar este site.</p>
<p>Favor entrar em contato com o administrador.</p>
```

### Fluxo da Execução

```
Requisição GET /home
        │
        ▼
┌─────────────────────────────┐
│   Middleware CheckPermission │
│   permission == false?       │
│   └── Sim ──► View acesso-  │
│               negado (403)   │
│   └── Não ──► Próximo        │
└─────────────────────────────┘
        │
        ▼
   HomeController@index
        │
        ▼
    View home.blade.php
```

Como a permissão é **false**, a middleware retorna diretamente a view `acesso-negado` com status `403`, e o controller **nunca é executado**.

## Conclusão

A atividade demonstra o fluxo de uma middleware no Laravel:

1. O **controller** é acionado pela rota.
2. A **middleware** intercepta a requisição antes do controller.
3. Ao negar permissão, a middleware envia a mensagem **"Você não tem permissão para acessar este site. Favor entrar em contato com o administrador."** na view, com o status HTTP **403 Forbidden**.