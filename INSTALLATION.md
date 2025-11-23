# Guide d'installation - ViralQuiz

## Problème : "Impossible de supprimer l'extension actuelle"

Si vous rencontrez cette erreur lors de la mise à jour, suivez ces étapes :

### Solution 1 : Installation manuelle via FTP/cPanel

1. **Désactiver le plugin** dans WordPress (Extensions → Extensions installées)

2. **Supprimer manuellement le dossier** via FTP/cPanel :
   - Connectez-vous à votre serveur via FTP ou cPanel File Manager
   - Allez dans `/wp-content/plugins/`
   - Supprimez complètement le dossier `viral-quiz` ou `viral-quiz-2` ou `viral-quiz-3`

3. **Extraire le ZIP localement** :
   - Téléchargez `viral-quiz.zip`
   - Extrayez-le sur votre ordinateur
   - Vous obtiendrez un dossier `viral-quiz`

4. **Uploader le dossier complet** :
   - Via FTP : Uploadez tout le contenu du dossier `viral-quiz` dans `/wp-content/plugins/viral-quiz/`
   - Via cPanel : Créez le dossier `viral-quiz` et uploadez tous les fichiers

5. **Vérifier les permissions** :
   - Fichiers : 644
   - Dossiers : 755

6. **Activer le plugin** dans WordPress

### Solution 2 : Via l'interface WordPress (méthode propre)

1. **Désactiver le plugin** (Extensions → Extensions installées)

2. **Supprimer le plugin** (bouton "Supprimer" - cela devrait fonctionner maintenant)

3. **Installer le nouveau plugin** :
   - Extensions → Ajouter
   - Téléverser le plugin
   - Sélectionnez `viral-quiz.zip`
   - Cliquez sur "Installer maintenant"

4. **Activer le plugin**

### Solution 3 : Si les fichiers sont verrouillés

Si les fichiers sont verrouillés par un processus :

1. **Via SSH** (si vous avez accès) :
   ```bash
   cd /wp-content/plugins/
   rm -rf viral-quiz*
   ```

2. **Via cPanel File Manager** :
   - Sélectionnez le dossier
   - Cliquez sur "Supprimer"
   - Confirmez la suppression

3. **Puis suivez la Solution 1** pour uploader le nouveau plugin

## Vérification après installation

Après installation, vérifiez que tous les fichiers sont présents :

1. Accédez à : `https://votresite.com/wp-content/plugins/viral-quiz/check-files.php`
2. Tous les fichiers doivent être marqués avec ✓

## Structure de fichiers attendue

```
wp-content/plugins/viral-quiz/
├── viral-quiz.php
├── check-files.php
├── readme.txt
├── admin/
│   ├── class-admin-menu.php
│   ├── class-admin-metabox.php
│   ├── admin-scripts.js
│   └── admin-styles.css
├── includes/
│   ├── class-cpt.php
│   ├── class-db.php
│   ├── class-rest-api.php
│   └── helpers.php
└── public/
    ├── class-shortcode.php
    ├── class-frontend-render.php
    ├── frontend.js
    └── frontend.css
```

## Support

Si le problème persiste, vérifiez :
- Les permissions des fichiers (644/755)
- L'espace disque disponible
- Les logs d'erreur PHP dans cPanel
- Que le plugin n'est pas actif lors de la suppression

