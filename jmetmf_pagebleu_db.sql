-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mar. 05 nov. 2024 à 17:46
-- Version du serveur : 10.5.23-MariaDB-0+deb11u1
-- Version de PHP : 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `jmetmf_pagebleu_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `Activite`
--

CREATE TABLE `Activite` (
  `id` int(11) NOT NULL,
  `filiere` enum('CIEL-Electronique','CIEL-Informatique','MELEC','BTS','Passerelle') NOT NULL,
  `nom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Adresse`
--

CREATE TABLE `Adresse` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `rue` varchar(255) NOT NULL,
  `code_postal` varchar(5) NOT NULL,
  `commune` varchar(255) NOT NULL,
  `pays` varchar(255) NOT NULL,
  `lieu_dit` varchar(255) DEFAULT NULL,
  `complement` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Contact`
--

CREATE TABLE `Contact` (
  `id` int(11) NOT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Entreprises`
--

CREATE TABLE `Entreprises` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `adresse_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `juridique_id` int(11) DEFAULT NULL,
  `lasallien` tinyint(1) DEFAULT 0,
  `checked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Entreprises_Activite`
--

CREATE TABLE `Entreprises_Activite` (
  `entreprise_id` int(11) NOT NULL,
  `activite_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Juridique`
--

CREATE TABLE `Juridique` (
  `id` int(11) NOT NULL,
  `SIREN` char(9) DEFAULT NULL,
  `SIRET` char(14) DEFAULT NULL,
  `RSC` varchar(255) NOT NULL,
  `creation` date DEFAULT NULL,
  `forme` varchar(255) DEFAULT NULL,
  `activite` varchar(255) NOT NULL,
  `activite_main` varchar(255) NOT NULL,
  `employés` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `success` tinyint(1) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Stage`
--

CREATE TABLE `Stage` (
  `id` int(11) NOT NULL,
  `entreprise_id` int(11) NOT NULL,
  `tuteur_id` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Tuteur`
--

CREATE TABLE `Tuteur` (
  `id` int(11) NOT NULL,
  `entreprise_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `poste` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Users`
--

CREATE TABLE `Users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt_time` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Activite`
--
ALTER TABLE `Activite`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `Adresse`
--
ALTER TABLE `Adresse`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `Contact`
--
ALTER TABLE `Contact`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `Entreprises`
--
ALTER TABLE `Entreprises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adresse_id` (`adresse_id`),
  ADD KEY `contact_id` (`contact_id`),
  ADD KEY `juridique_id` (`juridique_id`),
  ADD KEY `idx_entreprise_nom` (`nom`);

--
-- Index pour la table `Entreprises_Activite`
--
ALTER TABLE `Entreprises_Activite`
  ADD PRIMARY KEY (`entreprise_id`,`activite_id`),
  ADD KEY `activite_id` (`activite_id`);

--
-- Index pour la table `Juridique`
--
ALTER TABLE `Juridique`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `Stage`
--
ALTER TABLE `Stage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entreprise_id` (`entreprise_id`),
  ADD KEY `tuteur_id` (`tuteur_id`),
  ADD KEY `idx_stage_dates` (`date_debut`,`date_fin`);

--
-- Index pour la table `Tuteur`
--
ALTER TABLE `Tuteur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tuteur_nom` (`nom`),
  ADD KEY `entreprise_id` (`entreprise_id`);

--
-- Index pour la table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Activite`
--
ALTER TABLE `Activite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Adresse`
--
ALTER TABLE `Adresse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Contact`
--
ALTER TABLE `Contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Entreprises`
--
ALTER TABLE `Entreprises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Juridique`
--
ALTER TABLE `Juridique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Stage`
--
ALTER TABLE `Stage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Tuteur`
--
ALTER TABLE `Tuteur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Entreprises`
--
ALTER TABLE `Entreprises`
  ADD CONSTRAINT `Entreprises_ibfk_1` FOREIGN KEY (`adresse_id`) REFERENCES `Adresse` (`id`),
  ADD CONSTRAINT `Entreprises_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `Contact` (`id`),
  ADD CONSTRAINT `Entreprises_ibfk_3` FOREIGN KEY (`juridique_id`) REFERENCES `Juridique` (`id`);

--
-- Contraintes pour la table `Entreprises_Activite`
--
ALTER TABLE `Entreprises_Activite`
  ADD CONSTRAINT `Entreprises_Activite_ibfk_1` FOREIGN KEY (`entreprise_id`) REFERENCES `Entreprises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Entreprises_Activite_ibfk_2` FOREIGN KEY (`activite_id`) REFERENCES `Activite` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `Stage`
--
ALTER TABLE `Stage`
  ADD CONSTRAINT `Stage_ibfk_1` FOREIGN KEY (`entreprise_id`) REFERENCES `Entreprises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Stage_ibfk_2` FOREIGN KEY (`tuteur_id`) REFERENCES `Tuteur` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
