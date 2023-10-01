describe("Sign in", () => {
    before(() => {
        // create the database from schema.sql
        /*
        cy.readFile("./docker-entrypoint-initdb.d/test.sql").then((sqlCode) => {
          cy.task("queryDb", sqlCode);
        });

        This gives the following error message:

        ou have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'USE test;

        DROP TABLE IF EXISTS `users`;
        CREATE TABLE `users` (
                    ' at line 3

        https://on.cypress.io/api/task

        Because this error occurred during a `before all` hook we are skipping the remaining tests in the current suite: `Sign in`
        */

        cy.recreateDatabase();

        cy.visit("/sign-up");
        cy.get(`[data-cy="sign-up__email"]`).type("student@salle.url.edu");
        cy.get(`[data-cy="sign-up__password"]`).type("Test001");
        cy.get(`[data-cy="sign-up__btn"]`).click();
    });

    // after(() => {
    //   cy.task("queryDb",`DELETE FROM test.users where email = "student@salle.url.edu"`);
    // });

    it("shows the sign-in page", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in"]`).should("exist");
        cy.get(`[data-cy="sign-in__email"]`).should("exist");
        cy.get(`[data-cy="sign-in__password"]`).should("exist");
    });

    it("allows the user to sign-in correctly", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student@salle.url.edu");
        cy.get(`[data-cy="sign-in__password"]`).type("Test001");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.location("pathname").should("eq", "/");
    });

    it("shows error when email does not have salle.url.edu", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student@gmail.com");
        cy.get(`[data-cy="sign-in__password"]`).type("Test001");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongEmail"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongEmail"]`)
            .invoke("text")
            .should("eq", "Only emails from the domain @salle.url.edu are accepted.");
    });

    it("shows error when email is not a valid email", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student");
        cy.get(`[data-cy="sign-in__password"]`).type("Test001");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongEmail"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongEmail"]`)
            .invoke("text")
            .should("eq", "The email address is not valid");
    });

    it("shows error when password has less than 6 characters", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student@salle.url.edu");
        cy.get(`[data-cy="sign-in__password"]`).type("Test");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongPassword"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongPassword"]`)
            .invoke("text")
            .should("eq", "The password must contain at least 6 characters.");
    });

    it("shows error when password does not follow correct format", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student@salle.url.edu");
        cy.get(`[data-cy="sign-in__password"]`).type("TestTest");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongPassword"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongPassword"]`)
            .invoke("text")
            .should(
                "eq",
                "The password must contain both upper and lower case letters and numbers"
            );
    });

    it("shows error when user does not exist", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type(
            "nicolemarie.jimenez@salle.url.edu"
        );
        cy.get(`[data-cy="sign-in__password"]`).type("Test001");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongEmail"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongEmail"]`)
            .invoke("text")
            .should("eq", "User with this email address does not exist.");

        it("shows error when email and password do not match", () => {
            cy.visit("/sign-in");
            cy.get(`[data-cy="sign-in__email"]`).type("student@salle.url.edu");
            cy.get(`[data-cy="sign-in__password"]`).type("Test002");
            cy.get(`[data-cy="sign-in__btn"]`).click();
            cy.get(`[data-cy="sign-in__wrongPassword"]`).should("exist");
            cy.get(`[data-cy="sign-in__wrongPassword"]`)
                .invoke("text")
                .should("eq", "Your email and/or password are incorrect.");
        });
    });
});
