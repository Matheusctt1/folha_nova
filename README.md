# Folha Nova

![Status](https://img.shields.io/badge/status-Finalizado%20porem%20recebendo%20melhorias-green)
![Projeto Faculdade](https://img.shields.io/badge/Projeto-Faculdade-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange?logo=mysql)

> Biblioteca digital simples — sistema de anúncios de livros (PHP + MySQL).  
> Ponto de entrada: `index.php`.

---

## Status
Arquivos principais presentes no projeto: `index.php`, `principal.php`, `listar.php`, `anuncio.php`, `anuncio_popup.php`, `editar_anuncio.php`, `perfil.php`, `logout.php` e pasta `backend/`.

---

## Visão rápida
Projeto em PHP que usa MySQL para persistência. Login por `email` ou `nome`; criação/edição/exclusão de anúncios via scripts em `backend/`. Interface com CSS em `assets/css/`.

---

## Como rodar localmente
1. Coloque a pasta do projeto dentro de `htdocs` do XAMPP.  
2. Inicie **Apache** e **MySQL** no painel do XAMPP.  
3. Crie um banco no phpMyAdmin e importe o dump SQL do projeto (se houver).  
4. Ajuste `backend/config.php` com as credenciais locais (exemplo XAMPP abaixo).  
5. Verifique permissão de escrita em `uploads/`.  
6. Acesse no navegador:
```
http://localhost/<nome_da_pasta>/index.php
```

### Exemplo de `backend/config.php` (XAMPP)
```php
<?php
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';        // XAMPP padrão
$DB_NAME = 'folha_nova';
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die('Erro de conexão: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
```

---

## Estrutura do projeto
```
/
├─ assets/
│  └─ css/
├─ backend/
│  ├─ config.php
│  ├─ anuncio.php
│  ├─ anuncio_update.php
│  ├─ anuncio_delete.php
│  ├─ api_anuncio.php
│  ├─ api_anuncios.php
│  └─ cadastro.php
├─ uploads/
├─ index.php
├─ principal.php
├─ listar.php
├─ anuncio.php
├─ anuncio_popup.php
├─ editar_anuncio.php
└─ perfil.php
```

---

## Endpoints e handlers
- `backend/cadastro.php` — cria usuários.  
- `backend/anuncio.php` — cria anúncios.  
- `backend/anuncio_update.php` — edita anúncios.  
- `backend/anuncio_delete.php` — remove anúncios.  
- `backend/api_anuncio.php` / `api_anuncios.php` — dados JSON utilizados pelo frontend.

---
