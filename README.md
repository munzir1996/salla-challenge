# Salla Coding Challenge

This coding challenge is part of our technical recruitment process for a Senior Backend Engineer role. Please submit your solution within 7 calendar days. If you need clarification, feel free to send an email with your questions. Do not share your results publicly (e.g., no GitHub, no public blog posts).

## Products Table Structure

This is a table structure for the products that have an index in the id column to optimize performance searching:

```sql

CREATE TABLE `products` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`name` varchar(255) DEFAULT NULL,
`sku` varchar(255) DEFAULT NULL,
`price` decimal(7,2) DEFAULT NULL,
`currency` varchar(20) DEFAULT NULL,
`variations` text DEFAULT NULL,
`quantity` int(11) unsigned DEFAULT NULL,
`status` varchar(255) DEFAULT NULL,
`deleted_at` datetime NULL,
`delete_hint` varchar(255) DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1016 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE UNIQUE INDEX id_index ON Colleges (id);

```

### 1. Design Pattern!

Repository pattern:
Repository Pattern was used in this project to separates the application logic from the underlying data storage mechanism and
which makes it easier to maintain and test the application

### 2. Import Products From Excel!

To import products from Excel to Products table run the following command

php artisan command import:products

note:
the excel file should be in the project root, the command will run products-test as an example

### 3: Integrate with an External Data Source

To import products from specific API to Products table run the following command

php artisan command products:sync

Third-party supplier API. The product information endpoint is:

**https://5fc7a13cf3c77600165d89a8.mockapi.io/api/v5/products**

the command is scheduled daily at 12am.

## **General Hints**

In order to use the database queue driver and Laravel's job batching feature , you will need a database table to hold the jobs.
run the following command.

php artisan make:queue-table
php artisan make:queue-batches-table
php artisan migrate
