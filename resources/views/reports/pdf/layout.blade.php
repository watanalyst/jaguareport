<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Relatório')</title>
    <style>
        @page {
            margin: 120px 30px 100px 30px;
        }

        body {
            counter-reset: page;
        }

        .page-number:before {
            content: "Página " counter(page);
        }

        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 100px;
        }

        footer {
            position: fixed;
            bottom: -100px;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            font-size: 10px;
        }

        * {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .font-size-nove {
            font-size: 9px;
        }

        h2 {
            font-size: 20px;
            margin: 0;
        }

        h3 {
            font-size: 14px;
            margin: 0;
        }

        .borda-divisao-dois {
            border-bottom: 2px solid #000;
        }

        .borda-divisao {
            border-bottom: 1px solid #000;
        }

        .borda-footer {
            border-top: 1px solid #000;
        }

        .comissao-header,
        .comissao-valor {
            font-size: 13px;
        }
    </style>
    @yield('styles')
</head>
<body>
    @yield('content')
</body>
</html>
