CREATE DATABASE IF NOT EXISTS roombook;
USE roombook;

-- Tables
CREATE TABLE salles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50),
    capacite INT,
    equipements TEXT
);

CREATE TABLE employes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50),
    email VARCHAR(100),
    departement VARCHAR(50)
);

CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    salle_id INT,
    employe_id INT,
    date_reservation DATE,
    heure_debut TIME,
    heure_fin TIME,
    titre VARCHAR(100),
    statut ENUM('en_attente','confirmee','annulee') DEFAULT 'en_attente',
    FOREIGN KEY(salle_id) REFERENCES salles(id),
    FOREIGN KEY(employe_id) REFERENCES employes(id)
);

-- DONNÉES
INSERT INTO salles VALUES 
(1,'Salle Horizon',8,'Vidéoprojecteur, WiFi'),
(2,'Salle Aurora',12,'Vidéoprojecteur, Paperboard'),
(3,'Salle Polaris',20,'Tableau interactif');

INSERT INTO employes VALUES 
(1,'Jean Dupont','jean@esisa.ma','IT'),
(2,'Marie Martin','marie@esisa.ma','RH'),
(3,'Pierre Durand','pierre@esisa.ma','Marketing');

INSERT INTO reservations VALUES 
(1,1,1,'2024-12-09','10:00:00','11:30:00','Réunion IT','confirmee'),
(2,2,2,'2024-12-09','14:00:00','15:30:00','Entretien RH','confirmee');