-- Создание таблицы sport
CREATE TABLE
    `sport` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255),
        PRIMARY KEY (`id`),
        UNIQUE (`id`)
    );

-- Создание таблицы athlete
CREATE TABLE
    `athlete` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `sport_id` INT,
        `name` VARCHAR(255),
        `biography` VARCHAR(255),
        `birthday` DATE,
        `image_url` VARCHAR(255),
        PRIMARY KEY (`id`),
        FOREIGN KEY (`sport_id`) REFERENCES `sport` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
    );