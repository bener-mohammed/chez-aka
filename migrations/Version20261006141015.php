<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial Chez Aka database schema.
 */
final class Version20261006141015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial Chez Aka database schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE app_user (
                user_id INT AUTO_INCREMENT NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(180) NOT NULL,
                phone_number VARCHAR(20) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL,
                is_active TINYINT DEFAULT 1 NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT NULL,
                UNIQUE INDEX UNIQ_88BDF3E9E7927C74 (email),
                PRIMARY KEY (user_id)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE customer_order (
                order_id INT AUTO_INCREMENT NOT NULL,
                order_number VARCHAR(30) NOT NULL,
                pickup_time TIME NOT NULL,
                status VARCHAR(30) NOT NULL,
                total_amount NUMERIC(10, 2) NOT NULL,
                special_request LONGTEXT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT NULL,
                service_day_id INT NOT NULL,
                user_id INT NOT NULL,
                UNIQUE INDEX UNIQ_3B1CE6A3551F0F81 (order_number),
                INDEX IDX_3B1CE6A337054410 (service_day_id),
                INDEX IDX_3B1CE6A3A76ED395 (user_id),
                PRIMARY KEY (order_id)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE order_item (
                quantity INT NOT NULL,
                unit_price NUMERIC(10, 2) NOT NULL,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                INDEX IDX_52EA1F098D9F6D38 (order_id),
                INDEX IDX_52EA1F094584665A (product_id),
                PRIMARY KEY (order_id, product_id)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE payment (
                payment_id INT AUTO_INCREMENT NOT NULL,
                provider VARCHAR(50) NOT NULL,
                transaction_reference VARCHAR(255) DEFAULT NULL,
                amount NUMERIC(10, 2) NOT NULL,
                status VARCHAR(20) NOT NULL,
                payment_method VARCHAR(30) NOT NULL,
                created_at DATETIME NOT NULL,
                paid_at DATETIME DEFAULT NULL,
                order_id INT NOT NULL,
                UNIQUE INDEX UNIQ_6D28840DED84D250 (transaction_reference),
                INDEX IDX_6D28840D8D9F6D38 (order_id),
                PRIMARY KEY (payment_id)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE product (
                product_id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(150) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                price NUMERIC(10, 2) NOT NULL,
                is_available TINYINT DEFAULT 1 NOT NULL,
                is_click_collect TINYINT DEFAULT 0 NOT NULL,
                display_order INT DEFAULT 0 NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT NULL,
                category_id INT NOT NULL,
                INDEX IDX_D34A04AD12469DE2 (category_id),
                PRIMARY KEY (product_id)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE product_category (
                category_id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(100) NOT NULL,
                display_order INT DEFAULT 0 NOT NULL,
                is_active TINYINT DEFAULT 1 NOT NULL,
                UNIQUE INDEX UNIQ_CDFC73565E237E06 (name),
                PRIMARY KEY (category_id)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE reservation (
                reservation_id INT AUTO_INCREMENT NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(180) NOT NULL,
                phone_number VARCHAR(20) NOT NULL,
                reservation_time TIME NOT NULL,
                party_size INT NOT NULL,
                area_preference VARCHAR(20) NOT NULL,
                confirmed_area VARCHAR(20) DEFAULT NULL,
                status VARCHAR(20) NOT NULL,
                special_request LONGTEXT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                processed_at DATETIME DEFAULT NULL,
                service_day_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                INDEX IDX_42C8495537054410 (service_day_id),
                INDEX IDX_42C84955A76ED395 (user_id),
                PRIMARY KEY (reservation_id)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE restaurant_event (
                event_id INT AUTO_INCREMENT NOT NULL,
                title VARCHAR(150) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                start_datetime DATETIME NOT NULL,
                end_datetime DATETIME DEFAULT NULL,
                image_path VARCHAR(255) DEFAULT NULL,
                is_published TINYINT DEFAULT 0 NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT NULL,
                service_day_id INT NOT NULL,
                UNIQUE INDEX UNIQ_71B3B97637054410 (service_day_id),
                PRIMARY KEY (event_id)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE service_day (
                service_day_id INT AUTO_INCREMENT NOT NULL,
                service_date DATE NOT NULL,
                is_open TINYINT DEFAULT 1 NOT NULL,
                indoor_capacity INT DEFAULT 25 NOT NULL,
                terrace_capacity INT DEFAULT 14 NOT NULL,
                max_online_party_size INT DEFAULT 12 NOT NULL,
                click_collect_enabled TINYINT DEFAULT 1 NOT NULL,
                click_collect_order_limit INT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT NULL,
                UNIQUE INDEX UNIQ_C7F05AE764DD8396 (service_date),
                PRIMARY KEY (service_day_id)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE messenger_messages (
                id BIGINT AUTO_INCREMENT NOT NULL,
                body LONGTEXT NOT NULL,
                headers LONGTEXT NOT NULL,
                queue_name VARCHAR(190) NOT NULL,
                created_at DATETIME NOT NULL,
                available_at DATETIME NOT NULL,
                delivered_at DATETIME DEFAULT NULL,
                INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (
                    queue_name,
                    available_at,
                    delivered_at,
                    id
                ),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB'
        );

        $this->addSql(
            'ALTER TABLE customer_order
            ADD CONSTRAINT FK_3B1CE6A337054410
            FOREIGN KEY (service_day_id)
            REFERENCES service_day (service_day_id)
            ON DELETE RESTRICT'
        );

        $this->addSql(
            'ALTER TABLE customer_order
            ADD CONSTRAINT FK_3B1CE6A3A76ED395
            FOREIGN KEY (user_id)
            REFERENCES app_user (user_id)
            ON DELETE RESTRICT'
        );

        $this->addSql(
            'ALTER TABLE order_item
            ADD CONSTRAINT FK_52EA1F098D9F6D38
            FOREIGN KEY (order_id)
            REFERENCES customer_order (order_id)
            ON DELETE CASCADE'
        );

        $this->addSql(
            'ALTER TABLE order_item
            ADD CONSTRAINT FK_52EA1F094584665A
            FOREIGN KEY (product_id)
            REFERENCES product (product_id)
            ON DELETE RESTRICT'
        );

        $this->addSql(
            'ALTER TABLE payment
            ADD CONSTRAINT FK_6D28840D8D9F6D38
            FOREIGN KEY (order_id)
            REFERENCES customer_order (order_id)
            ON DELETE RESTRICT'
        );

        $this->addSql(
            'ALTER TABLE product
            ADD CONSTRAINT FK_D34A04AD12469DE2
            FOREIGN KEY (category_id)
            REFERENCES product_category (category_id)
            ON DELETE RESTRICT'
        );

        $this->addSql(
            'ALTER TABLE reservation
            ADD CONSTRAINT FK_42C8495537054410
            FOREIGN KEY (service_day_id)
            REFERENCES service_day (service_day_id)
            ON DELETE RESTRICT'
        );

        $this->addSql(
            'ALTER TABLE reservation
            ADD CONSTRAINT FK_42C84955A76ED395
            FOREIGN KEY (user_id)
            REFERENCES app_user (user_id)
            ON DELETE SET NULL'
        );

        $this->addSql(
            'ALTER TABLE restaurant_event
            ADD CONSTRAINT FK_71B3B97637054410
            FOREIGN KEY (service_day_id)
            REFERENCES service_day (service_day_id)
            ON DELETE RESTRICT'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE customer_order
            DROP FOREIGN KEY FK_3B1CE6A337054410'
        );

        $this->addSql(
            'ALTER TABLE customer_order
            DROP FOREIGN KEY FK_3B1CE6A3A76ED395'
        );

        $this->addSql(
            'ALTER TABLE order_item
            DROP FOREIGN KEY FK_52EA1F098D9F6D38'
        );

        $this->addSql(
            'ALTER TABLE order_item
            DROP FOREIGN KEY FK_52EA1F094584665A'
        );

        $this->addSql(
            'ALTER TABLE payment
            DROP FOREIGN KEY FK_6D28840D8D9F6D38'
        );

        $this->addSql(
            'ALTER TABLE product
            DROP FOREIGN KEY FK_D34A04AD12469DE2'
        );

        $this->addSql(
            'ALTER TABLE reservation
            DROP FOREIGN KEY FK_42C8495537054410'
        );

        $this->addSql(
            'ALTER TABLE reservation
            DROP FOREIGN KEY FK_42C84955A76ED395'
        );

        $this->addSql(
            'ALTER TABLE restaurant_event
            DROP FOREIGN KEY FK_71B3B97637054410'
        );

        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE customer_order');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE restaurant_event');
        $this->addSql('DROP TABLE service_day');
        $this->addSql('DROP TABLE messenger_messages');
    }
}