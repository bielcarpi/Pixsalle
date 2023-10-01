// ***********************************************************
// This example support/index.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import './commands'

// Alternatively you can use CommonJS syntax:
// require('./commands')

var productionEnv;
var testEnv;

const cypressDirPath = `./cypress`;
const cypressTmpDirPath = `${cypressDirPath}/tmp`
const tmpProductionEnvFilePath = `${cypressTmpDirPath}/.env.prod`;
const testEnvFilePath = `./.env`;
const dumpFile = `${cypressTmpDirPath}/dump.sql`;
const createFile = `${cypressTmpDirPath}/create.sql`;

// Database connection settings.
function exportFrom() {
    return {
        host: "db",
        user: "root",
        password: productionEnv.MYSQL_ROOT_PASSWORD,
        database: productionEnv.MYSQL_DATABASE
    }
}

function importTo() {
    return {
        host: "db",
        user: "root",
        password: testEnv.MYSQL_ROOT_PASSWORD,
        database: testEnv.MYSQL_DATABASE
    }
}

const createDatabaseStatement = `
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS test; 
CREATE DATABASE test;
`

function recreateTestDatabase() {
    // Dump pixsalle database
    cy.exec(`mysqldump -u${exportFrom().user} -p${exportFrom().password} -h${exportFrom().host} --no-data ${exportFrom().database} | sed 's/ AUTO_INCREMENT=[0-9]*//g' > ${dumpFile}`)
        .then(() => cy.exec(`echo "${createDatabaseStatement}" > ${createFile}`))
        .then(() => cy.exec(`mysql -u${importTo().user} -p${importTo().password} -h${importTo().host} < ${createFile}`))
        .then(() => cy.exec(`mysql -u${importTo().user} -p${importTo().password} -h${importTo().host} ${importTo().database} < ${dumpFile}`));
}


Cypress.Commands.add('recreateDatabase', recreateTestDatabase);

before(() => {
    // Read the production .env file, copy it to a safe location, change the actual (not the copied) .env file to
    // use a test database
    cy.readFile(testEnvFilePath, 'utf-8')
        .then((str) => cy.writeFile(tmpProductionEnvFilePath, str, 'utf-8'))
        .then(() => cy.task('dotenv', tmpProductionEnvFilePath).then((e) => productionEnv = e))
        .then(() => cy.task('setEnvValue', {path: testEnvFilePath, key: 'MYSQL_DATABASE', value: 'test'}))
        .then(() => cy.task('dotenv', testEnvFilePath).then((e) => testEnv = e));
});

after(() => {
    // Remove temporary files and swap .env files
    cy.readFile(tmpProductionEnvFilePath, 'utf-8')
        .then((str) => cy.writeFile(testEnvFilePath, str, 'utf-8'));
});