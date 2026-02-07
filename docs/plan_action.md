# Plan d'Action

## 1. Cadrage & Architecture Logicielle

- [x] Modélisation Base de Données (MCD/MLD)
  - [x] MConcevoir le MCD (Modèle Conceptuel) : Lister Entités & Relations. 
  - [x] Déduire le MLD (Modèle Logique) : Définir les tables SQL.
  - [x] Définir le modèle NoSQL pour les statistiques.

- [x] Découpage Fonctionnel & Dynamique
  - [x] Lister toutes les vues (Inventaire des pages)
  - [x] Définir le "Routing Table" (URLs et Méthodes associées)
  - [x] Définir l'architecture MVC
  - [x] Modélisation des Diagrammes de Cas d'Utilisation
  - [x] Modélisation des Diagrammes de Séquence 

## 2. Initialisation & Gestion de Projet

- [ ] Initialisation Git & Stratégie de Branches
  - [ ] Création du dépôt.
  - [ ] Création de la branche main.
  - [ ] Création de la branche developpement.

- [ ] Environnement Technique
  - [ ] Initialisation Symfony.
  - [ ] Configuration .env (MySQL + MongoDB).
  - [ ] Création des fichiers SQL d'initialisation

## 3. Maquettage & Design

- [ ] Charte Graphique (PDF)
  - [ ] Définir Palette de couleurs charte_graphique.md.
  - [ ] Définir Polices.

- [ ] Maquettes (Wireframes & Mockups)
  - [ ] 3 vues Bureau (Accueil, Liste Menus, Détail Menu).
  - [ ] 3 vues Mobile (Accueil, Liste Menus, Détail Menu).

## 4. Développement Front-End & Intégration

- [ ] Structure & Navigation
  - [ ] Header, Footer (Horaires, Mentions, CGV).
  - [ ] Page d'accueil (Présentation, Avis).

- [ ] Catalogue & Filtres
  - [ ] Page "Tous les menus" (HTML/CSS).
  - [ ] Faire le JS pour les filtres (Prix, Thème, Régime).

## 5. Développement Back-End

- [ ] Base de Données Relationnelle (MySQL)
  - [ ] Création du schéma (User, Menu, Commande, etc.).
  - [ ] Script d'insertion des données de test (SQL).
 
- [ ] Logique Métier (Fonctionnalités)
  - [ ] Authentification : Visiteur, Utilisateur, Employé, Admin.
  - [ ] Gestion de Contenu : CRUD Menus/Plats (Employé/Admin).
  - [ ] Tunnel de Commande :
    - [ ] Calcul auto (Réduction 10%, Frais livraison).
    - [ ] Envoi d'emails (Bienvenue, Confirmation).

- [ ] Base de Données NoSQL (MongoDB)
  - [ ] Connecteur MongoDB.
  - [ ] Page Stats Admin.

## 6. Livraison & Déploiement

- [ ] Déploiement (ex: Fly.io ou Heroku)
  - [ ] Configuration du déploiement.

- [ ] Mise en ligne Base de Données.
  - [ ] Validation
  - [ ] Vérification RGAA (Accessibilité).
  - [ ] Test du parcours complet sur l'URL publique.

## 7. Documentation Finale

- [ ] Manuel d'Utilisation (PDF)

- [ ] Documentation Technique (PDF)
  - [ ] Justification choix technique.
  - [x] MCD, Diagrammes.
  - [ ] Procédure de déploiement (README.md).


