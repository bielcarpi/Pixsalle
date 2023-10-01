describe('Blog API', () => {

    function createUsers(nUsers) {
        for (let i = 1; i <= nUsers; i++) {
            cy.visit("/sign-up");
            cy.get(`[data-cy="sign-up__email"]`).type(`student${i}@salle.url.edu`);
            cy.get(`[data-cy="sign-up__password"]`).type(`Test00${i}`);
            cy.get(`[data-cy="sign-up__btn"]`).click();
        }
    }

    function generateBlogPostPerUserId(userId) {
        const blogPost = {
            title: `Chapter 5: Slim for userId ${userId}`,
            content: 'Today we learned about Slim and this is an example.',
            userId: userId
        }

        return blogPost;
    }

    function createBlogPostsPerUser(postsForEachUser, nUsers) {
        let blogPosts = [];
        for (let u = 1; u <= nUsers; u++) {
            for (let p = 1; p <= postsForEachUser; p++) {
                let blogPost = generateBlogPostPerUserId(u)
                let response = cy.request('POST', '/api/blog', blogPost)
                blogPosts.push({post: blogPost, response: response});

            }
        }
        return blogPosts;
    }

    // This runs before each test
    beforeEach(() => {
        // recreate the database from schema.sql
        cy.recreateDatabase()
    })

    /**
     * CREATE
     */
    it('[B-1] adds a new blog post', () => {
        createUsers(1)

        let postWithUserId = generateBlogPostPerUserId(1)

        cy.request('POST', '/api/blog', postWithUserId)
            .then((response) => {
                expect(response.body).to.have.property('title', postWithUserId.title)

                expect(response.status).to.eq(201)
                expect(response.body).to.have.property('title', postWithUserId.title)
                expect(response.body).to.have.property('content', postWithUserId.content)
            })
    })

    it('[B-2] when creating a blog post, returns 400 if title or content or userId keys are missing', () => {
        cy.request({method: 'POST', url: '/api/blog', failOnStatusCode: false}, {})
            .then((response) => {
                const message = "'title' and/or 'content' and/or 'userId' key missing";

                expect(response.status).to.eq(400)
                expect(response.body).to.have.property('message', message)
            })
    })

    /**
     * READ
     */
    it('[B-3] gets a JSON response', () => {
        cy.request('/api/blog').its('headers').its('content-type').should('include', 'application/json')
    })

    it('[B-4] the post retrieved is the same as the post created', () => {
        createUsers(1)

        let blogPost = createBlogPostsPerUser(1, 1)[0].response.then((response) => {
            let postedPost = response.body
            cy.request('GET', `/api/blog/${postedPost.id}`).then((response) => {
                expect(response.status).to.eq(200)
                expect(response.body).to.have.property('id', postedPost.id)
                expect(response.body).to.have.property('title', postedPost.title)
                expect(response.body).to.have.property('content', postedPost.content)
            })
        })
    })

    it('[B-5] throws an error when requested for a non-existing blog post', () => {
        const message = "Blog entry with id 1 does not exist"
        cy.request({url: `/api/blog/1`, failOnStatusCode: false}).then((response) => {
            expect(response.status).to.eq(404)
            expect(response.body).to.have.property('message', message)
        })
    })

    it('[B-6] when creating 3 posts, 3 posts are retrieved', () => {
        createUsers(3)

        let responses = createBlogPostsPerUser(1, 3)

        cy.request('GET', `/api/blog`).then((response) => {
            expect(response.body).to.have.lengthOf(3)
        })
    })

    /**
     * UPDATE
     */
    it('[B-7] updates a blog post', () => {
        createUsers(1)

        let blogPost = createBlogPostsPerUser(1, 1)[0].response.then((response) => {
            let updateBlogpost = response.body
            updateBlogpost.title = 'Updated title'
            updateBlogpost.content = 'Updated description'

            cy.request('PUT', `/api/blog/${updateBlogpost.userId}`, updateBlogpost).then((response) => {
                expect(response.status).to.eq(200)
                expect(response.body).to.have.property('title', updateBlogpost.title)
                expect(response.body).to.have.property('content', updateBlogpost.content)
            })
        })
    })

    it('[B-8] when updating a blog post, returns 400 if title or content keys are missing', () => {
        createUsers(1)

        let blogPost = createBlogPostsPerUser(1, 1)[0].response.then((response) => {
            cy.request({method: 'PUT', url: `/api/blog/${response.id}`, failOnStatusCode: false, body: {}})
                .then((response) => {
                    const message = "'title' and/or 'content' key missing";

                    expect(response.status).to.eq(400)
                    expect(response.body).to.have.property('message', message)
                })
        })
    })

    it('[B-9] when updating a blog post, returns 404 if the blog post is not found', () => {
        const message = "Blog entry with id 1 does not exist"
        cy.request({method: 'PUT', url: `/api/blog/1`, failOnStatusCode: false, body: generateBlogPostPerUserId(1)}).then((response) => {
            expect(response.status).to.eq(404)
            expect(response.body).to.have.property('message', message)
        })
    })

    /**
     * DELETE
     */
    it('[B-10] deletes a blog post', () => {
        createUsers(1)
        let blogPost = createBlogPostsPerUser(1, 1)[0].response.then((createResponse) => {
            cy.request('DELETE', `/api/blog/${createResponse.body.id}`).then((response) => {
                const message = `Blog entry with id ${createResponse.body.id} was successfully deleted`

                expect(response.status).to.eq(200)
                expect(response.body).to.have.property('message', message)
            })
        })
    })

    it('[B-11] throws an error when requested to delete a non-existing blog post', () => {
        const message = "Blog entry with id 1 does not exist"
        cy.request({
            method: 'DELETE',
            url: `/api/blog/1`,
            failOnStatusCode: false
        }).then((response) => {
            expect(response.status).to.eq(404)
            expect(response.body).to.have.property('message', message)
        })
    })
})