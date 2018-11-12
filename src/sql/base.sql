SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

-- --------------------------------------------------------

--
-- Table structure for table `directory_poll_queue`
--

DROP TABLE IF EXISTS `directory_poll_queue`;
CREATE TABLE `directory_poll_queue` (
  `directory_url` varchar(190) NOT NULL,
  `added`         datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_polled`   datetime              DEFAULT NULL,
  `next_poll`     datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `retries_count` int(11)      NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `photo`
--

DROP TABLE IF EXISTS `photo`;
CREATE TABLE `photo` (
  `profile_id` int(11)    NOT NULL,
  `data`       mediumblob NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
CREATE TABLE `profile` (
  `id`            int(11)      NOT NULL,
  `name`          char(255)    NOT NULL,
  `server_id`     int(11)      NOT NULL,
  `username`      varchar(100) NOT NULL,
  `addr`          varchar(150) NOT NULL,
  `account_type`  varchar(20)  NOT NULL DEFAULT 'People',
  `pdesc`         char(255)    NOT NULL,
  `locality`      char(255)    NOT NULL,
  `region`        char(255)    NOT NULL,
  `country`       char(255)    NOT NULL,
  `profile_url`   char(255)    NOT NULL,
  `dfrn_request`  varchar(250)          DEFAULT NULL,
  `photo`         char(255)    NOT NULL,
  `tags`          longtext     NOT NULL,
  `filled_fields` tinyint(4)   NOT NULL DEFAULT '0',
  `last_activity` varchar(7)            DEFAULT NULL,
  `available`     tinyint(1)   NOT NULL DEFAULT '1',
  `created`       datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated`       datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP
  ON UPDATE CURRENT_TIMESTAMP,
  `hidden`        tinyint(4)   NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `profile_poll_queue`
--

DROP TABLE IF EXISTS `profile_poll_queue`;
CREATE TABLE `profile_poll_queue` (
  `profile_url`   varchar(190) NOT NULL,
  `added`         datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_polled`   datetime              DEFAULT NULL
  ON UPDATE CURRENT_TIMESTAMP,
  `next_poll`     datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `retries_count` int(11)      NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `server`
--

DROP TABLE IF EXISTS `server`;
CREATE TABLE `server` (
  `id`            int(10) UNSIGNED NOT NULL,
  `base_url`      varchar(190)     NOT NULL,
  `path`          varchar(190)     NOT NULL,
  `health_score`  int(11)          NOT NULL DEFAULT '0',
  `noscrape_url`  varchar(255)              DEFAULT NULL,
  `first_noticed` datetime         NOT NULL,
  `last_seen`     datetime                  DEFAULT NULL,
  `name`          varchar(255)              DEFAULT NULL,
  `version`       varchar(255)              DEFAULT NULL,
  `addons`        mediumtext,
  `reg_policy`    char(32)                  DEFAULT NULL,
  `info`          text,
  `admin_name`    varchar(255)              DEFAULT NULL,
  `admin_profile` varchar(255)              DEFAULT NULL,
  `ssl_state`     bit(1)                    DEFAULT NULL,
  `ssl_grade`     varchar(3)                DEFAULT NULL,
  `available`     tinyint(1)       NOT NULL DEFAULT '1',
  `hidden`        tinyint(1)       NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `server_alias`
--

DROP TABLE IF EXISTS `server_alias`;
CREATE TABLE `server_alias` (
  `server_id` int(11)      NOT NULL,
  `alias`     varchar(190) NOT NULL,
  `timestamp` datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP
  ON UPDATE CURRENT_TIMESTAMP
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `server_poll_queue`
--

DROP TABLE IF EXISTS `server_poll_queue`;
CREATE TABLE `server_poll_queue` (
  `base_url`      varchar(190) NOT NULL,
  `added`         datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `request_count` int(11)      NOT NULL DEFAULT '1',
  `last_polled`   datetime              DEFAULT NULL
  ON UPDATE CURRENT_TIMESTAMP,
  `next_poll`     datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `retries_count` int(11)      NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `site_probe`
--

DROP TABLE IF EXISTS `site_probe`;
CREATE TABLE `site_probe` (
  `server_id`    int(10) UNSIGNED NOT NULL,
  `timestamp`    datetime         NOT NULL,
  `request_time` int(10) UNSIGNED NOT NULL,
  `avg_ping`     int(11) DEFAULT NULL,
  `speed_score`  int(11) DEFAULT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `site_scrape`
--

DROP TABLE IF EXISTS `site_scrape`;
CREATE TABLE `site_scrape` (
  `id`           int(10) UNSIGNED NOT NULL,
  `server_id`    int(10) UNSIGNED NOT NULL,
  `performed`    datetime         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `request_time` int(10) UNSIGNED NOT NULL,
  `scrape_time`  int(10) UNSIGNED NOT NULL,
  `photo_time`   int(10) UNSIGNED NOT NULL,
  `total_time`   int(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `profile_id` int(11)   NOT NULL,
  `term`       char(255) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `directory_poll_queue`
--
ALTER TABLE `directory_poll_queue`
  ADD PRIMARY KEY (`directory_url`);

--
-- Indexes for table `photo`
--
ALTER TABLE `photo`
  ADD UNIQUE KEY `profile_id` (`profile_id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `addr` (`addr`),
  ADD UNIQUE KEY `profile_url` (`profile_url`(190)),
  ADD KEY `profile_sorting` (`filled_fields`, `last_activity`, `updated`),
  ADD KEY `site_id` (`server_id`);
ALTER TABLE `profile`
  ADD FULLTEXT KEY `profile-ft` (`name`, `pdesc`, `profile_url`, `locality`, `region`, `country`);

--
-- Indexes for table `profile_poll_queue`
--
ALTER TABLE `profile_poll_queue`
  ADD PRIMARY KEY (`profile_url`);

--
-- Indexes for table `server`
--
ALTER TABLE `server`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `base_url` (`base_url`) USING BTREE,
  ADD KEY `health_score` (`health_score`),
  ADD KEY `last_seen` (`last_seen`) USING BTREE;

--
-- Indexes for table `server_alias`
--
ALTER TABLE `server_alias`
  ADD PRIMARY KEY (`alias`, `server_id`);

--
-- Indexes for table `server_poll_queue`
--
ALTER TABLE `server_poll_queue`
  ADD PRIMARY KEY (`base_url`);

--
-- Indexes for table `site_probe`
--
ALTER TABLE `site_probe`
  ADD PRIMARY KEY (`server_id`, `timestamp`);

--
-- Indexes for table `site_scrape`
--
ALTER TABLE `site_scrape`
  ADD PRIMARY KEY (`id`),
  ADD KEY `performed` (`performed`) USING BTREE,
  ADD KEY `server_id` (`server_id`) USING BTREE;

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`profile_id`, `term`(190)) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `server`
--
ALTER TABLE `server`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_scrape`
--
ALTER TABLE `site_scrape`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
