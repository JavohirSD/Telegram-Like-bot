# Telegram Like bot
Telegram Like bot with inline mode support

[![Build Status](https://travis-ci.org/joemccann/dillinger.svg?branch=master)](https://core.telegram.org/bots/api)

### Installation

1. Upload index.php to your Apache or Ngnix web server
2. Set webhook url
3. Add inline mode to your bot (if required)
4. Setup database
5. Enjoy !

### Minimal database structure
You can modify database by adding more columns like a 'username', 'first name', 'last name' for future deep analyses and optimisations

```sql
CREATE TABLE `likes` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`chat_id`    VARCHAR(255) NOT NULL,
	`query_id`   VARCHAR(255) NOT NULL,
	`message_id` VARCHAR(255) NOT NULL,
	`user_id`    VARCHAR(255) NULL DEFAULT NULL,
	`created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `query_id` (`query_id`)
)

COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
```

### Author
  - ###### Javohir Abdirasulov
   -  alienware7x@gmail.com
   -  Telegram.me/JavohirAB

License
----

MIT

**Free Software, Hell Yeah!**
