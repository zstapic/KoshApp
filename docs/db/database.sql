-- KoshApp - MySQL 8.0.16+ (potreban za CHECK)
-- NOTE: Ovaj file je samo za pregled i ručno testiranje. Prava baza se builda s Laravel migracijama.

SET NAMES utf8mb4;

CREATE TABLE `savez` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `korisnik` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_savez` BIGINT UNSIGNED NULL COMMENT 'NULL samo za SuperAdmin',
  `korisnicko_ime` VARCHAR(255) NOT NULL,
  `lozinka_hash` VARCHAR(255) NOT NULL,
  `uloga` ENUM('AdminSaveza', 'Zapisnicar', 'SuperAdmin') NOT NULL,
  `aktivan` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE KEY `korisnik_korisnicko_ime_unique` (`korisnicko_ime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `klub` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(255) NOT NULL,
  `id_savez` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kategorija` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(255) NOT NULL,
  `max_dob` INT NULL,
  `id_savez` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sezona` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(255) NOT NULL,
  `godina_pocetka` INT NOT NULL,
  `godina_zavrsetka` INT NOT NULL,
  `id_savez` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `liga` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(255) NOT NULL,
  `id_kategorija` BIGINT UNSIGNED NOT NULL,
  `id_savez` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `izdanje_lige` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_liga` BIGINT UNSIGNED NOT NULL,
  `id_sezona` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `izdanje_lige_liga_sezona_unique` (`id_liga`, `id_sezona`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `selekcija` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(255) NOT NULL,
  `id_klub` BIGINT UNSIGNED NOT NULL,
  `id_kategorija` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ekipa_u_ligi` (
  `id_izdanje_lige` BIGINT UNSIGNED NOT NULL,
  `id_selekcija` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_izdanje_lige`, `id_selekcija`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `igrac` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ime` VARCHAR(255) NOT NULL,
  `prezime` VARCHAR(255) NOT NULL,
  `datum_rodenja` DATE NOT NULL,
  `broj_licence` VARCHAR(255) NOT NULL,
  `id_klub` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `nastupa_za_selekciju` (
  `id_igrac` BIGINT UNSIGNED NOT NULL,
  `id_selekcija` BIGINT UNSIGNED NOT NULL,
  `id_sezona` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_igrac`, `id_selekcija`, `id_sezona`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `trener` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ime` VARCHAR(255) NOT NULL,
  `prezime` VARCHAR(255) NOT NULL,
  `broj_licence` VARCHAR(255) NOT NULL,
  `id_klub` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sudac` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ime` VARCHAR(255) NOT NULL,
  `prezime` VARCHAR(255) NOT NULL,
  `id_savez` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dvorana` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `naziv` VARCHAR(255) NOT NULL,
  `mjesto` VARCHAR(255) NOT NULL,
  `id_savez` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `utakmica` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `broj_utakmice` INT NOT NULL,
  `kolo` INT NULL,
  `id_izdanje_lige` BIGINT UNSIGNED NOT NULL,
  `id_selekcija_domacin` BIGINT UNSIGNED NOT NULL,
  `id_selekcija_gost` BIGINT UNSIGNED NOT NULL,
  `datum_vrijeme` DATETIME NOT NULL,
  `id_dvorana` BIGINT UNSIGNED NULL,
  `id_glavni_sudac` BIGINT UNSIGNED NULL,
  `id_sudac_1` BIGINT UNSIGNED NULL,
  `id_sudac_2` BIGINT UNSIGNED NULL,
  `id_zapisnicar` BIGINT UNSIGNED NULL COMMENT 'dodjeljuje admin',
  `pomocni_zapisnicar` VARCHAR(255) NULL,
  `mjeritelj_vremena` VARCHAR(255) NULL,
  `operater_24s` VARCHAR(255) NULL,
  `status` ENUM('Zakazana', 'U_tijeku', 'Zavrsena', 'Predana') NOT NULL DEFAULT 'Zakazana',
  `pocetak` DATETIME NULL,
  `id_token_pokretanja` BIGINT UNSIGNED NULL COMMENT 'Sanctum token (uredaj) koji je zapoceo utakmicu',
  `zavrsetak` DATETIME NULL,
  `rezultat_domacin` INT NULL COMMENT 'upisuje se pri zavrsetku, iz dogadaja',
  `rezultat_gost` INT NULL COMMENT 'upisuje se pri zavrsetku, iz dogadaja',
  `prosvjed` BOOLEAN NOT NULL DEFAULT FALSE,
  `prosvjed_napomena` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `utakmica_izdanje_broj_unique` (`id_izdanje_lige`, `broj_utakmice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `utakmica_sastav` (
  `id_utakmica` BIGINT UNSIGNED NOT NULL,
  `id_igrac` BIGINT UNSIGNED NOT NULL,
  `id_selekcija` BIGINT UNSIGNED NOT NULL,
  `broj_dresa` INT NOT NULL,
  `kapetan` BOOLEAN NOT NULL DEFAULT FALSE,
  `u_prvoj_petorci` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id_utakmica`, `id_igrac`),
  UNIQUE KEY `utakmica_sastav_dres_unique` (`id_utakmica`, `id_selekcija`, `broj_dresa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `utakmica_trener` (
  `id_utakmica` BIGINT UNSIGNED NOT NULL,
  `id_selekcija` BIGINT UNSIGNED NOT NULL,
  `id_trener` BIGINT UNSIGNED NOT NULL,
  `uloga` ENUM('Glavni', 'Pomocni') NOT NULL,
  PRIMARY KEY (`id_utakmica`, `id_selekcija`, `uloga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `aktivnost` (
  `id` CHAR(36) NOT NULL COMMENT 'UUID v4 koji generira tablet',
  `redni_broj` INT NOT NULL,
  `id_utakmica` BIGINT UNSIGNED NOT NULL,
  `tip` ENUM('Kos_1', 'Kos_2', 'Kos_3', 'Osobna_P', 'Tehnicka_T', 'Nesportska_U',
             'Diskvalifikacijska_D', 'Tehnicka_C', 'Tehnicka_B', 'Zamjena',
             'Timeout', 'HCC', 'Zavrsi_cetvrtinu') NOT NULL,
  `id_igrac` BIGINT UNSIGNED NULL,
  `id_trener` BIGINT UNSIGNED NULL,
  `id_selekcija` BIGINT UNSIGNED NULL,
  `id_igrac_izlazi` BIGINT UNSIGNED NULL,
  `cetvrtina` INT NOT NULL,
  `minuta` INT NULL,
  `kreirano` DATETIME NOT NULL,
  `ponisten` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`),
  UNIQUE KEY `aktivnost_utakmica_redni_broj_unique` (`id_utakmica`, `redni_broj`),
  CONSTRAINT `aktivnost_igrac_ili_trener` CHECK (`id_igrac` IS NULL OR `id_trener` IS NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Strani kljucevi

ALTER TABLE `korisnik` ADD FOREIGN KEY (`id_savez`) REFERENCES `savez` (`id`);
ALTER TABLE `klub` ADD FOREIGN KEY (`id_savez`) REFERENCES `savez` (`id`);
ALTER TABLE `kategorija` ADD FOREIGN KEY (`id_savez`) REFERENCES `savez` (`id`);
ALTER TABLE `sezona` ADD FOREIGN KEY (`id_savez`) REFERENCES `savez` (`id`);
ALTER TABLE `liga` ADD FOREIGN KEY (`id_savez`) REFERENCES `savez` (`id`);
ALTER TABLE `sudac` ADD FOREIGN KEY (`id_savez`) REFERENCES `savez` (`id`);
ALTER TABLE `dvorana` ADD FOREIGN KEY (`id_savez`) REFERENCES `savez` (`id`);

ALTER TABLE `liga` ADD FOREIGN KEY (`id_kategorija`) REFERENCES `kategorija` (`id`);
ALTER TABLE `izdanje_lige` ADD FOREIGN KEY (`id_liga`) REFERENCES `liga` (`id`);
ALTER TABLE `izdanje_lige` ADD FOREIGN KEY (`id_sezona`) REFERENCES `sezona` (`id`);
ALTER TABLE `selekcija` ADD FOREIGN KEY (`id_klub`) REFERENCES `klub` (`id`);
ALTER TABLE `selekcija` ADD FOREIGN KEY (`id_kategorija`) REFERENCES `kategorija` (`id`);
ALTER TABLE `ekipa_u_ligi` ADD FOREIGN KEY (`id_izdanje_lige`) REFERENCES `izdanje_lige` (`id`);
ALTER TABLE `ekipa_u_ligi` ADD FOREIGN KEY (`id_selekcija`) REFERENCES `selekcija` (`id`);

ALTER TABLE `igrac` ADD FOREIGN KEY (`id_klub`) REFERENCES `klub` (`id`);
ALTER TABLE `trener` ADD FOREIGN KEY (`id_klub`) REFERENCES `klub` (`id`);
ALTER TABLE `nastupa_za_selekciju` ADD FOREIGN KEY (`id_igrac`) REFERENCES `igrac` (`id`);
ALTER TABLE `nastupa_za_selekciju` ADD FOREIGN KEY (`id_selekcija`) REFERENCES `selekcija` (`id`);
ALTER TABLE `nastupa_za_selekciju` ADD FOREIGN KEY (`id_sezona`) REFERENCES `sezona` (`id`);

ALTER TABLE `utakmica` ADD FOREIGN KEY (`id_izdanje_lige`) REFERENCES `izdanje_lige` (`id`);
ALTER TABLE `utakmica` ADD FOREIGN KEY (`id_selekcija_domacin`) REFERENCES `selekcija` (`id`);
ALTER TABLE `utakmica` ADD FOREIGN KEY (`id_selekcija_gost`) REFERENCES `selekcija` (`id`);
ALTER TABLE `utakmica` ADD FOREIGN KEY (`id_dvorana`) REFERENCES `dvorana` (`id`);
ALTER TABLE `utakmica` ADD FOREIGN KEY (`id_glavni_sudac`) REFERENCES `sudac` (`id`);
ALTER TABLE `utakmica` ADD FOREIGN KEY (`id_sudac_1`) REFERENCES `sudac` (`id`);
ALTER TABLE `utakmica` ADD FOREIGN KEY (`id_sudac_2`) REFERENCES `sudac` (`id`);
ALTER TABLE `utakmica` ADD FOREIGN KEY (`id_zapisnicar`) REFERENCES `korisnik` (`id`);

ALTER TABLE `utakmica_sastav` ADD FOREIGN KEY (`id_utakmica`) REFERENCES `utakmica` (`id`);
ALTER TABLE `utakmica_sastav` ADD FOREIGN KEY (`id_igrac`) REFERENCES `igrac` (`id`);
ALTER TABLE `utakmica_sastav` ADD FOREIGN KEY (`id_selekcija`) REFERENCES `selekcija` (`id`);

ALTER TABLE `utakmica_trener` ADD FOREIGN KEY (`id_utakmica`) REFERENCES `utakmica` (`id`);
ALTER TABLE `utakmica_trener` ADD FOREIGN KEY (`id_selekcija`) REFERENCES `selekcija` (`id`);
ALTER TABLE `utakmica_trener` ADD FOREIGN KEY (`id_trener`) REFERENCES `trener` (`id`);

ALTER TABLE `aktivnost` ADD FOREIGN KEY (`id_utakmica`) REFERENCES `utakmica` (`id`);
ALTER TABLE `aktivnost` ADD FOREIGN KEY (`id_igrac`) REFERENCES `igrac` (`id`);
ALTER TABLE `aktivnost` ADD FOREIGN KEY (`id_trener`) REFERENCES `trener` (`id`);
ALTER TABLE `aktivnost` ADD FOREIGN KEY (`id_selekcija`) REFERENCES `selekcija` (`id`);
ALTER TABLE `aktivnost` ADD FOREIGN KEY (`id_igrac_izlazi`) REFERENCES `igrac` (`id`);
