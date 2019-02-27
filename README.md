# Boilerplate | DOCKER - PHP

**_És aqui um 'Boilerplate' em docker_**


<!-- [referencia](http://www.phprs.com.br/2016/05/criando-um-ambiente-de-desenvolvimento-php-com-docker-compose/) -->

## Sobre Docker
[documentação](https://docs.docker.com/)

## Temos aqui:
> Cenário para desenvolvimento PHP

## Servidor:
- PHP: 5.6-apache

# Passo a Passo

## Arquivo docker-compose.yml
Precisamos criar um arquivo com nome 'docker-compose' e sua extensão sendo '.yml'
apartir disso adicionar as informações abaixo.

### Container PHP
<pre>...
php:
  build: .
  ports:
   - "80:80"
   - "443:443"
  volumes:
   - ./www:/var/www/html
...</pre>

> Para executar usamos o comando 'docker-compose up'
