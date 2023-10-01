/// <reference types="cypress" />
// ***********************************************************
// This example plugins/index.js can be used to load plugins
//
// You can change the location of this file or turn off loading
// the plugins file with the 'pluginsFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/plugins-guide
// ***********************************************************
const fs = require("fs");
const os = require("os");
const path = require("path");

// read .env file & convert to array
const readEnvVars = (envFilePath) => fs.readFileSync(envFilePath, "utf-8").split(os.EOL);

/**
 * Finds the key in .env files and returns the corresponding value
 *
 * @param envFilePath
 * @param {string} key Key to find
 * @returns {string|null} Value of the key
 */
const getEnvValue = (envFilePath, key) => {
    // find the line that contains the key (exact match)
    const matchedLine = readEnvVars(envFilePath).find((line) => line.split("=")[0] === key);
    // split the line (delimiter is '=') and return the item at index 2
    return matchedLine !== undefined ? matchedLine.split("=")[1] : null;
};

/**
 * Updates value for existing key or creates a new key=value line
 *
 * This function is a modified version of https://stackoverflow.com/a/65001580/3153583
 *
 * @param envFilePath
 * @param {string} key Key to update/insert
 * @param {string} value Value to update/insert
 */
const setEnvValue = (envFilePath, key, value) => {
    const envVars = readEnvVars(envFilePath);
    const targetLine = envVars.find((line) => line.split("=")[0] === key);
    if (targetLine !== undefined) {
        // update existing line
        const targetLineIndex = envVars.indexOf(targetLine);
        // replace the key/value with the new value
        envVars.splice(targetLineIndex, 1, `${key}=${value}`);
    } else {
        // create new key value
        envVars.push(`${key}="${value}"`);
    }
    // write everything back to the file system
    fs.writeFileSync(envFilePath, envVars.join(os.EOL));
};

function queryTestDb(query, config) {
    // creates a new mysql connection using credentials from cypress.json env's
    const connection = mysql.createConnection(config.env.db);
    // start connection to db
    connection.connect();
    // exec query + disconnect to db as a Promise
    return new Promise((resolve, reject) => {
        connection.query(query, (error, results) => {
            if (error) reject(error);
            else {
                connection.end();
                return resolve(results);
            }
        });
    });
}

/**
 * @type {Cypress.PluginConfig}
 */
// eslint-disable-next-line no-unused-vars
module.exports = (on, config) => {
    // `on` is used to hook into various events Cypress emits
    // `config` is the resolved Cypress config
    // Usage: cy.task('queryDb', query)
    on("task", {
        queryDb: query => {
            return queryTestDb(query, config);
        },
        log(message) {
            console.log(message)
            return null
        },
        dotenv(path) {
            const result = require('dotenv').config({path: path, encoding: 'utf-8'});
            console.log(`dotenv; path: ${path}; res.parsed: `);
            console.log(result.parsed);
            return result.parsed;
        },
        getEnvValue({path, key}) {
            let value = getEnvValue(path, key);
            console.log("Value: " + value);
            return value;
        },
        setEnvValue({path, key, value}) {
            setEnvValue(path, key, value);
            return null;
        },
        fileExists(filename) {
            return new Promise((resolve, reject) => {
                fs.exists(filename, (exists) => {
                    console.log("EXISTS: " + exists);
                    if (!exists) {
                        return reject(exists)
                    }
                    resolve(exists)
                })
            })
        },
    })
}
