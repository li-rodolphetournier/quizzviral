=== ViralQuiz ===
Contributors: tournier Rodolphe
Tags: quiz, trivia, personality, viral, interactive
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin WordPress pour créer et afficher des quiz viraux (Trivia & Personnalité).

== Description ==

ViralQuiz est un plugin WordPress complet qui permet de créer et d'afficher des quiz interactifs viraux. Le plugin supporte deux types de quiz :

* **Quiz Trivia** : Questions avec réponses correctes/incorrectes
* **Quiz Personnalité** : Questions avec système de scoring par points

== Fonctionnalités ==

* Custom Post Type dédié pour les quiz
* Interface d'administration intuitive avec repeater JavaScript
* Shortcode simple : [viral_quiz id="123"]
* Navigation fluide entre les questions
* Barre de progression
* Calcul automatique des scores
* Enregistrement des résultats en base de données
* API REST interne pour la récupération et la soumission des quiz
* Compatible avec Avada / Fusion Builder
* Design responsive et moderne

== Installation ==

1. Téléchargez le plugin
2. Uploadez le dossier `viral-quiz` dans `/wp-content/plugins/`
3. Activez le plugin via le menu 'Extensions' de WordPress
4. Créez votre premier quiz via le menu 'Quiz Viraux'
5. Utilisez le shortcode [viral_quiz id="X"] pour afficher un quiz

== Utilisation ==

1. **Créer un quiz** :
   - Allez dans "Quiz Viraux" > "Ajouter"
   - Remplissez le titre et la description
   - Sélectionnez le type de quiz (Trivia ou Personnalité)
   - Ajoutez vos questions et réponses via la metabox "Questions & Réponses"
   - Publiez le quiz

2. **Importer un quiz depuis JSON** :
   - Allez dans "Quiz Viraux" > "Importer JSON"
   - Uploadez un fichier JSON au format ci-dessous
   - Le quiz sera créé en brouillon, vous pourrez le modifier et le publier

3. **Afficher un quiz** :
   - Utilisez le shortcode : [viral_quiz id="123"]
   - Remplacez 123 par l'ID de votre quiz

4. **Quiz Trivia** :
   - Pour chaque question, sélectionnez la réponse correcte
   - Le résultat affiche le nombre de bonnes réponses

5. **Quiz Personnalité** :
   - Pour chaque réponse, définissez un score (nombre de points)
   - Le résultat affiche le score total
   - Pour les quiz de personnalité avec maisons (ex: Poudlard), ajoutez l'attribut "house" à chaque réponse

== Structure du plugin ==

```
viral-quiz/
├── viral-quiz.php (fichier principal)
├── readme.txt
├── admin/
│   ├── class-admin-menu.php
│   ├── class-admin-metabox.php
│   ├── admin-scripts.js
│   └── admin-styles.css
├── public/
│   ├── class-shortcode.php
│   ├── class-frontend-render.php
│   ├── frontend.js
│   └── frontend.css
├── includes/
│   ├── class-cpt.php
│   ├── class-rest-api.php
│   ├── class-db.php
│   └── helpers.php
└── assets/
    ├── css/
    └── js/
```

== Changelog ==

= 1.0.0 =
* Version initiale
* Custom Post Type "viral_quiz"
* Metabox avec repeater JavaScript
* Shortcode [viral_quiz]
* Navigation frontend
* Calcul des scores
* API REST interne
* Table MySQL pour les résultats
* Styles responsive

== Développement ==

Le plugin respecte les bonnes pratiques WordPress :
* Sécurité (sanitization, escaping, nonces)
* Code modulaire et organisé
* Documentation PHPDoc
* Compatibilité avec les thèmes WordPress
* Compatible Avada / Fusion Builder

== Importation JSON ==

Le plugin supporte l'importation de quiz depuis un fichier JSON. Voici un exemple de format :

=== Exemple : Quiz Trivia ===

{
  "title": "Quiz Culture Générale",
  "description": "Testez vos connaissances avec ce quiz de culture générale.",
  "type": "trivia",
  "questions": [
    {
      "question_text": "Quelle est la capitale de la France ?",
      "answers": [
        {
          "text": "Paris",
          "score": 0,
          "is_correct": true
        },
        {
          "text": "Lyon",
          "score": 0,
          "is_correct": false
        },
        {
          "text": "Marseille",
          "score": 0,
          "is_correct": false
        },
        {
          "text": "Toulouse",
          "score": 0,
          "is_correct": false
        }
      ]
    },
    {
      "question_text": "Qui a peint la Joconde ?",
      "answers": [
        {
          "text": "Picasso",
          "score": 0,
          "is_correct": false
        },
        {
          "text": "Van Gogh",
          "score": 0,
          "is_correct": false
        },
        {
          "text": "Léonard de Vinci",
          "score": 0,
          "is_correct": true
        },
        {
          "text": "Monet",
          "score": 0,
          "is_correct": false
        }
      ]
    }
  ]
}

=== Exemple : Quiz Personnalité Simple ===

{
  "title": "Quel type de personnalité êtes-vous ?",
  "description": "Découvrez votre profil de personnalité en répondant à ces questions.",
  "type": "personality",
  "questions": [
    {
      "question_text": "Que préférez-vous faire le week-end ?",
      "answers": [
        {
          "text": "Rester chez soi",
          "score": 5,
          "is_correct": false
        },
        {
          "text": "Sortir avec des amis",
          "score": 3,
          "is_correct": false
        },
        {
          "text": "Faire du sport",
          "score": 4,
          "is_correct": false
        },
        {
          "text": "Lire un livre",
          "score": 6,
          "is_correct": false
        }
      ]
    },
    {
      "question_text": "Quel est votre style de communication ?",
      "answers": [
        {
          "text": "Direct et franc",
          "score": 5,
          "is_correct": false
        },
        {
          "text": "Diplomatique",
          "score": 4,
          "is_correct": false
        },
        {
          "text": "Réservé",
          "score": 3,
          "is_correct": false
        },
        {
          "text": "Humoristique",
          "score": 6,
          "is_correct": false
        }
      ]
    }
  ]
}

=== Exemple : Quiz Personnalité avec Maisons (Poudlard) ===

{
  "title": "Test du Choixpeau : Quelle est votre maison à Poudlard ?",
  "description": "Découvrez votre maison de Poudlard !",
  "type": "personality",
  "questions": [
    {
      "question_text": "Quel animal fantastique préférez-vous ?",
      "answers": [
        {
          "text": "L'Hippogriffe",
          "score": 6,
          "house": "Serdaigle",
          "is_correct": false
        },
        {
          "text": "Le Phoenix",
          "score": 6,
          "house": "gryffondor",
          "is_correct": false
        },
        {
          "text": "Le Niffleur",
          "score": 6,
          "house": "poufsouffle",
          "is_correct": false
        },
        {
          "text": "Le Basilic",
          "score": 6,
          "house": "serpentard",
          "is_correct": false
        }
      ]
    },
    {
      "question_text": "Quelle couleur vous attire le plus ?",
      "answers": [
        {
          "text": "Rouge et or",
          "score": 5,
          "house": "gryffondor",
          "is_correct": false
        },
        {
          "text": "Bleu et bronze",
          "score": 5,
          "house": "serdaigle",
          "is_correct": false
        },
        {
          "text": "Vert et argent",
          "score": 5,
          "house": "serpentard",
          "is_correct": false
        },
        {
          "text": "Jaune et noir",
          "score": 5,
          "house": "poufsouffle",
          "is_correct": false
        }
      ]
    }
  ]
}

=== Format JSON - Champs requis ===

* **title** (requis) : Titre du quiz
* **description** (optionnel) : Description du quiz
* **type** (requis) : "trivia" ou "personality"
* **questions** (requis) : Tableau de questions

Chaque question doit contenir :
* **question_text** (requis) : Texte de la question
* **image** (optionnel) : URL de l'image de la question
* **image_id** (optionnel) : ID WordPress de l'image (si uploadée via WordPress)
* **answers** (requis) : Tableau de réponses (minimum 4)

Chaque réponse doit contenir :
* **text** (requis) : Texte de la réponse
* **score** (requis) : Score numérique (0 pour trivia, variable pour personality)
* **is_correct** (requis) : true/false (true pour la bonne réponse en trivia)
* **house** (optionnel) : Maison pour quiz personnalité (gryffondor, serdaigle, serpentard, poufsouffle)
* **image** (optionnel) : URL de l'image de la réponse
* **image_id** (optionnel) : ID WordPress de l'image (si uploadée via WordPress)

=== Instructions d'importation ===

1. Créez un fichier .json avec le format ci-dessus
2. Allez dans "Quiz Viraux" > "Importer JSON"
3. Uploadez votre fichier JSON
4. Le quiz sera créé en brouillon
5. Vérifiez et publiez le quiz

== Support ==

Pour toute question ou problème, contactez le développeur.

