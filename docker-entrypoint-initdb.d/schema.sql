SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE IF NOT EXISTS pixsalle;
USE pixsalle;

DROP TABLE IF EXISTS users;
CREATE TABLE users(
    id INT AUTO_INCREMENT,
    email VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    phoneNumber VARCHAR(255) NOT NULL,
    profilePic VARCHAR(255),
    membership INT DEFAULT 0,
    funds FLOAT DEFAULT 30.0,
    createdAt DATETIME NOT NULL,
    updatedAt DATETIME NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE users AUTO_INCREMENT=501;


DROP TABLE IF EXISTS portfolio;
CREATE TABLE portfolio(
    user_id INT,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(1000) NOT NULL,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS album;
CREATE TABLE album(
    id INT AUTO_INCREMENT,
    portfolio_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE album AUTO_INCREMENT=1501;


DROP TABLE IF EXISTS photo;
CREATE TABLE photo(
    id INT AUTO_INCREMENT,
    album_id INT NOT NULL,
    link VARCHAR(1000) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS blog_entries;
CREATE TABLE blog_entries(
    id INT AUTO_INCREMENT,
    user_id INT NOT NULL,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE blog_entries AUTO_INCREMENT=2501;


-- Now that all tables are created, let's populate the database with test entries
INSERT INTO users (email, username, phoneNumber, password, membership, createdAt, updatedAt)
VALUES ('linustorvalds@salle.url.edu', 'Linus', '640577373', 'e64b78fc3bc91bcbc7dc232ba8ec59e0', 1, now(), now()); -- Password: Admin123

INSERT INTO users (email, username, phoneNumber, password, membership, createdAt, updatedAt)
VALUES ('jamesgosling@salle.url.edu',  'James', '640577373', 'e64b78fc3bc91bcbc7dc232ba8ec59e0', 1, now(), now()); -- Password: Admin123

INSERT INTO users (email, username, phoneNumber, password, membership, createdAt, updatedAt)
VALUES ('alanturing@salle.url.edu',  'Alan', '640577373', 'e64b78fc3bc91bcbc7dc232ba8ec59e0', 1, now(), now()); -- Password: Admin123


INSERT INTO portfolio (user_id, title, description)
VALUES ((SELECT id FROM users WHERE email = 'linustorvalds@salle.url.edu'), 'I build value through design', 'Hi, I\'m Linus! Designer. Product Person. Multidisciplinary designer who hacks at, makes and occasionally breaks things. Product Design Director on the team behind matrix.org. Less moody in real life. ✌️');

INSERT INTO portfolio (user_id, title, description)
VALUES ((SELECT id FROM users WHERE email = 'jamesgosling@salle.url.edu'), 'Hello. I am James.', 'I\'m an interdisciplinary designer living in San Francisco, California. Currently I work at Carbon Health, transforming how we experience healthcare. I hope you enjoy my albums and photos!');

INSERT INTO portfolio (user_id, title, description)
VALUES ((SELECT id FROM users WHERE email = 'alanturing@salle.url.edu'), 'With my camera, I capture daily life', 'I\'m a designer in London, UK. Currently, I\'m designing for operational analytics with the Census team. In my free time, I work on a project called Alloy Healthcare where I\'m making photos to Electronic Medical Record (EMR) that is easier to use.');


INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'linustorvalds@salle.url.edu'), 'Linux');
INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'linustorvalds@salle.url.edu'), 'Miami B.');
INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'linustorvalds@salle.url.edu'), 'C is Amazing');
INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'linustorvalds@salle.url.edu'), 'Indonesia');

INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'jamesgosling@salle.url.edu'), 'Java');
INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'jamesgosling@salle.url.edu'), 'SpaceX');

INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'alanturing@salle.url.edu'), 'World War 2');
INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'alanturing@salle.url.edu'), 'Turing Machine');
INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'alanturing@salle.url.edu'), 'Apollo 11');
INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'alanturing@salle.url.edu'), 'Mountain Bike');
INSERT INTO album (portfolio_id, title)
VALUES ((SELECT id FROM users WHERE email = 'alanturing@salle.url.edu'), 'Influencers');

INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Mountain Bike'), 'https://i.ytimg.com/vi/BlUNA216-fM/maxresdefault.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'SpaceX'), 'https://campusdata.uark.edu/resources/images/articles/2020-07-07_04-40-03-PMAllison-Ruck.jpg?width=800&mode=max');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Indonesia'), 'https://i0.wp.com/asiatimes.com/wp-content/uploads/2019/07/Indonesia-Bali-Temple-May-15-2017-iStock-e1563519099854.jpg?fit=1200%2C801&ssl=1');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Apollo 11'), 'https://ychef.files.bbci.co.uk/976x549/p07gz5td.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Linux'), 'https://i.blogs.es/105ff7/mint-tux/450_1000.jpeg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Influencers'), 'https://i0.wp.com/hipertextual.com/wp-content/uploads/2021/09/elon-musk_1.png?fit=1200%2C835&quality=50&strip=all&ssl=1');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Apollo 11'), 'https://cdn.mos.cms.futurecdn.net/mxMV3eZ8vJNqgyrnjrHg3B.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Mountain Bike'), 'https://www.multidaymtb.com/wp-content/uploads/VolcatCostaBrava-stage-race-recreation-tour-climb-sun-mountainbiking-Spain-MultidayMTB.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Apollo 11'), 'https://upload.wikimedia.org/wikipedia/commons/3/3d/Apollo_11_Crew.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'C is Amazing'), 'https://www.profesionalreview.com/wp-content/uploads/2020/05/Linus-Torvalds-deja-Intel-y-adopta-un-Threadripper-de-32-núcleos-en-su-PC.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Linux'), 'https://i.ytimg.com/vi/_Ua-d9OeUOg/maxresdefault.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'SpaceX'), 'https://as.com/diarioas/imagenes/2021/09/15/actualidad/1631732889_156135_1631732953_noticia_normal_recorte1.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'SpaceX'), 'https://cdnuploads.aa.com.tr/uploads/Contents/2021/09/14/thumbs_b_c_2444ce080be78c4dd57c25b1bbd5837c.jpg?v=060005');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Miami B.'), 'https://cdn2.hometogo.net/assets/media/pics/520_500/611aae220a8de.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Miami B.'), 'https://st3.depositphotos.com/1001146/14087/i/1600/depositphotos_140879866-stock-photo-aerial-view-of-miami-beach.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Java'), 'https://dxnews.com/upload/Image/Java-Island_YB1-PD1SA_DX-News.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Linux'), 'https://www.chrisstewart.ca/wp-content/uploads/2022/03/linux-image.jpeg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Turing Machine'), 'https://fudzilla.com/media/k2/items/cache/fb5886c3335e0d18deb24956f78715ec_XL.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'C is Amazing'), 'https://bullocksbuzz.com/wp-content/uploads/2017/07/C-Programming-Language.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'World War 2'), 'https://www.history.com/.image/ar_1:1%2Cc_fill%2Ccs_srgb%2Cfl_progressive%2Cq_auto:good%2Cw_1200/MTgwNzg0NjAzODk0NzE5NTc2/wwii-battles-gettyimages-538297253.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Linux'), 'https://www.lpi.org/sites/default/files/styles/w555/public/LPI-CODE_0.jpg?itok=mLPazE2t');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Java'), 'https://www.triptipedia.com/tip/img/wyqFN07hi.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Turing Machine'), 'https://qph.fs.quoracdn.net/main-qimg-abfe228796da8cede00fb86db122e204.webp');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Linux'), 'https://www.redeszone.net/app/uploads-redeszone.net/2017/09/programas-seguridad-linux.jpg?x=480&y=375&quality=40');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Java'), 'https://img.traveltriangle.com/blog/wp-content/uploads/2018/08/BorobudurtempleJavaIslandepb0310.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Java'), 'https://img.traveltriangle.com/blog/wp-content/uploads/2018/08/cover-java.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'World War 2'), 'https://cdn.aarp.net/content/dam/aarp/politics/events-and-history/2020/07/1140x655-iwo-jima-flag-raising.imgcache.rev.web.1044.600.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Mountain Bike'), 'https://www.esciclismo.com/actualidad/imagenes/a/volcat-et1-lideres-2022-ocisport-press.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Apollo 11'), 'https://upload.wikimedia.org/wikipedia/commons/0/04/Apollo_11_Lunar_Lander_-_5927_NASA.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'World War 2'), 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/Nürnberg_Reichsparteitag_Hitler_retouched.jpg/170px-Nürnberg_Reichsparteitag_Hitler_retouched.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Influencers'), 'https://imagenes.elpais.com/resizer/H_CSGYFikj7vdSIBnvZW4SP2KPE=/414x0/cloudfront-eu-central-1.images.arcpublishing.com/prisa/G7XBAG3ZGD7IWLG4OT2GA4XBEM.jpg');
INSERT INTO photo (album_id, link)
VALUES ((SELECT id FROM album WHERE title = 'Indonesia'), 'https://www.nationsonline.org/gallery/Indonesia/Piaynemo-West-Papua.jpg');

INSERT INTO blog_entries (user_id, title, content)
VALUES ((SELECT id FROM users WHERE email = 'linustorvalds@salle.url.edu'), 'I build value through design', 'Hi, I\'m Linus! Designer. Product Person. Multidisciplinary designer who hacks at, makes and occasionally breaks things. Product Design Director on the team behind matrix.org. Less moody in real life. ✌️');
INSERT INTO blog_entries (user_id, title, content)
VALUES ((SELECT id FROM users WHERE email = 'jamesgosling@salle.url.edu'), 'Hello. I am James.', 'I\'m an interdisciplinary designer living in San Francisco, California. Currently I work at Carbon Health, transforming how we experience healthcare. I hope you enjoy my albums and photos!');
