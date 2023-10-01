<h1 align="center">PixSalle</h1>

<p align="center">
  <a href="https://www.php.net">
    <img src="https://img.shields.io/badge/PHP-8-blue.svg">
  </a>
  <a href="https://github.com/slimphp/Slim">
    <img src="https://img.shields.io/badge/Slim-blue.svg">
  </a>
    <a href="https://opensource.org/licenses/BSD-3-Clause">
    <img src="https://img.shields.io/badge/Open%20Source-%E2%9D%A4-brightgreen.svg">
  </a>
  <a href="https://github.com/hexstorm9/AgeRoyale/tree/develop">
    <img src="https://img.shields.io/badge/Development Stage-blue.svg">
  </a>
</p>

<p align="center">
PixSalle is a new platform where students can freely showcase their work and express themselves through photography. This web application allows them to be members, explore photos posted by other photographers and picture editors, view resources that they can use to improve their work and contribute to the knowledge sharing within the community through blogs.
</p>

## Demo
In order to try the app, run ```docker compose up``` and you can use this demo account (the database is already populated with test data):
- User: alanturing@salle.url.edu
- Password: Admin123

By default, the app runs on ```localhost:8030```

### Explore
![Explore](res/screenshot2.png)

### Your Portfolio
![Portfolio](res/screenshot1.png)

## Cypress Tests
In order to run the cypress tests that are set up in cypress/integration/blog.spec.js, use the following bash command:
```bash
docker run --env CYPRESS_baseUrl=http://nginx:80 -v "${PWD}:/cypress" -w /cypress --network="pixsalle_default" -it --rm vcaballerosalle/cypress-mysql:1.0 /cypress --browser chrome --spec "cypress/integration/blog.spec.js"
```


## Authors
Biel Carpi (biel.carpi@outlook.com)