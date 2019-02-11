-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 11-Fev-2019 às 23:38
-- Versão do servidor: 5.7.25-0ubuntu0.16.04.2
-- PHP Version: 7.0.32-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `awm`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `Community`
--

CREATE TABLE `Community` (
  `id` int(10) NOT NULL,
  `iduser` int(10) NOT NULL,
  `idinstitution` int(10) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `body` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Content`
--

CREATE TABLE `Content` (
  `id` int(10) NOT NULL,
  `contributor` varchar(250) CHARACTER SET latin1 NOT NULL,
  `coverage` text CHARACTER SET latin1 NOT NULL,
  `creator` varchar(250) CHARACTER SET latin1 NOT NULL,
  `date` date NOT NULL,
  `description` text NOT NULL,
  `format` text NOT NULL,
  `identifier` varchar(250) NOT NULL,
  `language` varchar(8) NOT NULL,
  `publisher` varchar(250) NOT NULL,
  `source` text NOT NULL,
  `subject` varchar(250) NOT NULL,
  `title` varchar(250) NOT NULL,
  `type` varchar(250) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Content_Institutions`
--

CREATE TABLE `Content_Institutions` (
  `id` int(12) NOT NULL,
  `idcontent` int(9) NOT NULL,
  `idinstitution` int(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Institutions`
--

CREATE TABLE `Institutions` (
  `id` int(7) NOT NULL,
  `latitude` varchar(25) NOT NULL,
  `longitude` varchar(25) NOT NULL,
  `name` varchar(250) NOT NULL,
  `address` mediumtext NOT NULL,
  `city` varchar(250) NOT NULL,
  `district` varchar(250) DEFAULT NULL,
  `country` char(2) NOT NULL,
  `url` mediumtext NOT NULL,
  `email` varchar(250) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'waiting',
  `collaborator_name` varchar(250) DEFAULT NULL,
  `collaborator_email` varchar(250) DEFAULT NULL,
  `identifier` mediumtext,
  `importedfrom` mediumtext,
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Profiles`
--

CREATE TABLE `Profiles` (
  `id` int(10) NOT NULL,
  `iduser` int(10) NOT NULL,
  `genre` varchar(20) NOT NULL,
  `url` varchar(250) NOT NULL,
  `institution` varchar(250) NOT NULL,
  `education` varchar(250) NOT NULL,
  `country` varchar(2) NOT NULL,
  `district` varchar(100) NOT NULL,
  `city` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Users`
--

CREATE TABLE `Users` (
  `id` int(7) NOT NULL,
  `name` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `country` varchar(2) DEFAULT NULL,
  `hash` varchar(100) DEFAULT NULL,
  `privilege` varchar(20) NOT NULL DEFAULT 'mapper',
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `Users_Institutions`
--

CREATE TABLE `Users_Institutions` (
  `id` int(12) NOT NULL,
  `iduser` int(12) NOT NULL,
  `idinstitution` int(12) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Community`
--
ALTER TABLE `Community`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Content`
--
ALTER TABLE `Content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Content_Institutions`
--
ALTER TABLE `Content_Institutions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Institutions`
--
ALTER TABLE `Institutions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Profiles`
--
ALTER TABLE `Profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Users_Institutions`
--
ALTER TABLE `Users_Institutions`
  ADD PRIMARY KEY (`id`);

