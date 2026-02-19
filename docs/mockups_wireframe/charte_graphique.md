# Charte Graphique & UI - Spécifications Techniques

## 1. Palette de Couleurs & Contrastes

### Couleurs Principales (Marque & Action)
**Rouge Bordeaux**
    - Hex : `#800020`
    - Usage : Boutons d'action (CTA) majeurs (ex: "Commander", "Voir le menu").
**Or / Moutarde**
    - Hex : `#D4AF37`
    - Usage : étoiles d'avis, séparateurs.

### Couleurs Neutres (Structure)
**Gris Anthracite**
    - Hex : `#2C3E50`
    - Usage : Corps de texte standard.
**Blanc Crème**
    - Hex : `#F9F9F9`
    - Usage : Arrière-plan général (Background).

### Couleurs d'États & Feedback
**Vert Succès**
    - Hex : `#28A745`
    - Usage : Messages de confirmation, validation de formulaire.
**Rouge Erreur**
    - Hex : `#DC3545`
    - Usage : Messages d'erreur, champs invalides.

### Couleurs des Liens
**Lien d'action (ex: "DÉTAILS →")**
    - Couleur : Rouge Bordeaux (`#800020`)
    - Style : Non souligné, avec flèche →
**Lien hover**
    - Couleur : Rouge plus foncé (`#600018`)
**Lien texte classique**
    - Couleur : Rouge Bordeaux, souligné

### Boutons

**Bouton Principal**
    - Fond : Rouge Bordeaux (`#800020`)
    - Texte : Blanc
    - Hover : Fond plus foncé (`#600018`)
    - Focus : Bordure `3px solid #600018`
    - Disabled : Fond gris (`#CCCCCC`), texte (`#666666`)

**Bouton Secondaire**
    - Fond : Transparent
    - Bordure : `1px solid #800020`
    - Texte : Rouge Bordeaux (`#800020`)
    - Hover : Fond bordeaux, texte blanc

### Tags / Étiquettes (ex: "NOËL", "VEGAN")
- Fond : Rose clair (`#F8E8EC`)
- Texte : Rouge Bordeaux (`#800020`)
- Border-radius : `4px`

### Prix
- Couleur : Rouge Bordeaux (`#800020`)
- Police : `Playfair Display` (Serif)
- Taille : Plus grande que le texte courant

## Gestion des erreurs
Une erreur de formulaire doit être signalée par la couleur rouge ET une icône ou un texte explicite.*

## 2. Typographie
*Unités relatives (`rem` ou `em`) obligatoires. Pas de pixels fixes pour les font-sizes.*

### Titres (Headings)
**Police :** `Playfair Display` (Serif).

### Corps de texte (Body)
- Police : `Open Sans` (Sans-serif).

**Paramètres de base :**
    - Taille min : `16px` (1rem).
    - Line-height : `1.5` (aération pour dyslexie).

## 3. Formulaires
* **Champs :** Bordures contrastées (ratio > 3:1 avec le fond).
* **Labels :** Toujours visibles, placés au-dessus ou à gauche.
    * *Interdit :* Utiliser le `placeholder` comme seul label.

## 4. Mise en page (Layout)

* **Mobile :** Menu "Burger", architecture en colonne unique (stack).
* **Desktop :** Menu horizontal, grilles (grid) pour les menus.