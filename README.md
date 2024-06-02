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
