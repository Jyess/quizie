<div id="readme-top" align="center">
  <img src="https://raw.githubusercontent.com/Jyess/quiz_website/master/public/favicon.ico" alt="Logo" width="60">

  <h3 align="center">Quizie</h3>

  <p align="center">
    A web application to make quizes, collect answers and analyze them.
    <br><br>
    <a href="https://github.com/Jyess/quizie/issues">Report a bug</a>
  </p>
</div>

<details>
  <summary>Table of contents</summary>
  <ol>
    <li>
      <a href="#about">About</a>
      <ul>
        <li><a href="#built-with">Built with</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#installation-for-windows">Installation (for Windows)</a></li>
      </ul>
    </li>
    <li><a href="#contact">Contact</a></li>
  </ol>
</details>

## About
This project was made during my internship at Evalandgo (now called Qwesteo). The goal was to get used to the Symfony framework in a context that is the closest to 
what I was going to work with.

### Built with
[![Symfony][Symfony-img]][Symfony-url]
<br>
[![Php][Php-img]][Php-url]

## Getting started
### Installation (for Windows)
1. Clone the project
2. Download and install the Symfony CLI (https://symfony.com/download)
3. Download and install Composer (https://getcomposer.org/download/)
4. Download and install Yarn (https://classic.yarnpkg.com/lang/en/docs/install)
5. Go inside the project
6. Run `composer install`
7. Run `yarn install`
8. Run `yarn encore dev`
9. Run `php bin/console doctrine:database:create`
10. Run `php bin/console d:s:u --force`
11. Run `php bin/console doctrine:fixtures:load` (creates a user, random quizes, results...)
12. Start the server with `symfony server:start`
13. Log in with "admin@gmail.com" and the password "azerty"

## Contact
Axel IGHIR - <a mailto="axel.ighir@outlook.fr">axel.ighir@outlook.fr</a><br>
Project link: [Quizie](https://github.com/Jyess/quizie)

<p align="right"><a href="#readme-top">Back to top</a></p>

[Symfony-img]: https://img.shields.io/badge/symfony-black?style=for-the-badge&logo=symfony&logoColor=white
[Symfony-url]: https://symfony.com/
[Php-img]: https://img.shields.io/badge/php-787CB4?style=for-the-badge&logo=php&logoColor=white
[Php-url]: https://www.php.net/
