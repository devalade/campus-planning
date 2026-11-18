# CONTEXT

## Le projet

**Campus Planning** est une application web Laravel de gestion d'emploi du temps universitaire. Les professeurs programment leurs cours pour un groupe d'étudiants, l'administrateur gère les groupes, les étudiants consultent les cours de leurs groupes, et les participants reçoivent un email à chaque création, modification ou annulation.

L'interface est en français. Le code, les commentaires et les noms de domaine sont en anglais.

## Domaine

### Utilisateur (`User`)

- `role` : `Role::Admin` (`admin`), `Role::Professor` (`professor`) ou `Role::Student` (`student`).
- Un professeur possède ses cours via `courses()` (`professor_id`).
- Un étudiant appartient à des groupes via `groups()` (table pivot `group_student`).
- Authentification : Laravel Fortify, email vérifié obligatoire, email et mot de passe.

### Cours (`Course`)

- Champs : `professor_id`, `group_id`, `title`, `room`, `day_of_week`, `starts_at`, `ends_at`.
- `group()` donne le groupe qui suit le cours ; `enrolledStudents()` renvoie les étudiants de ce groupe.
- `day_of_week` est un enum `Weekday` (entier, 1 = lundi).
- `starts_at` et `ends_at` sont normalisés en `H:i:s`.
- `room` est un enum `Room` : Amphi A, B, C, Salles 01 à 30, En ligne.
- Le scope `visibleTo(User $user)` limite les cours au professeur propriétaire ou aux étudiants du groupe.

### Groupe (`Group`)

- Champ : `name` (unique).
- `students()` : les étudiants membres (table pivot `group_student`) ; `courses()` : les cours du groupe.
- Géré par l'administrateur sur `admin/groups` (gate `manage-groups`).

### Enums

| Enum | Valeurs |
| --- | --- |
| `Role` | `Admin`, `Professor`, `Student` |
| `Weekday` | `Monday` à `Sunday`, libellés français, `schedulable()` = lundi au samedi |
| `Room` | Amphi A/B/C, Salle 01/05/07/12/21/24/30, En ligne |
| `CourseChange` | `Created`, `Updated`, `Cancelled` |

## Règles métier

- Un professeur ne voit et ne modifie que ses propres cours (`CoursePolicy` + `visibleTo`).
- Un étudiant ne voit que les cours de ses groupes et ne peut pas les modifier.
- Chaque cours est programmé pour un groupe, choisi par le professeur parmi les groupes existants.
- Les cours sont planifiables du lundi au samedi.
- Les horaires vont de 08:00 à 20:00, avec une heure de début strictement antérieure à l'heure de fin.
- Un professeur ne peut pas avoir deux cours qui se chevauchent le même jour. Le contrôle ne porte pas encore sur la salle.
- Seuls les utilisateurs avec le rôle étudiant peuvent être membres d'un groupe. Seul l'administrateur gère les groupes et leurs membres.
- Un groupe utilisé par un cours ne peut pas être supprimé.
- Un email est envoyé à la création et à la modification (professeur + étudiants du groupe), et à l'annulation (étudiants du groupe).
- Un échec d'envoi d'email n'annule pas l'opération : un toast d'avertissement s'affiche.

## Décisions

### 2026-11-18 · Les inscriptions passent par des groupes, gérés par l'administrateur

- Un professeur n'inscrit plus d'étudiants à la main. Il choisit **un groupe** pour chaque cours.
- Un groupe (ex. « L3 Informatique », « L3 Info · TD 1 ») est créé par l'administrateur, qui en gère les membres. Seuls des étudiants peuvent être membres.
- Un étudiant peut appartenir à plusieurs groupes (promotion et groupe de TD).
- Un cours appartient à un seul groupe. Ses étudiants sont les membres du groupe, calculés à chaque lecture : un étudiant ajouté au groupe voit aussitôt les cours du groupe.
- Un groupe utilisé par un cours ne peut pas être supprimé.
- Raison : dans une université, la scolarité inscrit les étudiants dans des groupes ; le professeur ne choisit pas qui suit son cours.

### 2026-11-18 · Aucun code Livewire

- Toutes les pages (calendrier, cours, jours spéciaux, groupes, paramètres) utilisent des contrôleurs classiques, des vues Blade et des formulaires HTML (POST, PUT, PATCH, DELETE).
- La validation des cours vit dans `App\Http\Requests\CourseRequest` (règles, messages, jour spécial et chevauchement dans `after()`).
- Les tests sont des tests HTTP (`post()`, `assertSessionHasErrors()`), plus simples à lire pour des étudiants.
- Livewire, Flux UI et Blaze sont désinstallés. L'interface utilise des composants Blade maison (`resources/views/components/ui`) et Tailwind. Pas de framework JavaScript : la navigation mobile utilise `<details>`, la confirmation de suppression un `<dialog>` natif, le thème clair/sombre un petit script dans `partials/head`. Un test d'architecture interdit `Livewire` dans `app/`.
- Raison : rester aussi simple que possible pour un cours d'introduction au TDD.

### 2026-11-18 · Pas de double authentification ni de passkeys

- Connexion par email et mot de passe uniquement. Les fonctionnalités Fortify `twoFactorAuthentication` et `passkeys` sont retirées, avec leurs écrans et leurs tests.
- La page Sécurité ne sert plus qu'à changer de mot de passe.
- Les migrations des passkeys et des colonnes de double authentification sont supprimées. Il faut relancer `php artisan migrate:fresh --seed`.

## Fonctionnalités

- Calendrier hebdomadaire du lundi au samedi, grille 08:00 à 20:00.
- Les cours qui se chevauchent sont répartis en colonnes sur la grille.
- Statistiques de la semaine : nombre de cours et heures d'enseignement.
- Création, modification et annulation de cours via des pages de formulaire (`courses.create`, `courses.edit`).
- Notification email markdown (`mail.course-schedule`).
- Paramètres : profil, mot de passe, apparence, suppression de compte.

## Architecture

- Laravel 13, PHP 8.3+, SQLite par défaut.
- Pages métier : contrôleurs dans `app/Http/Controllers` (dont `Admin/`), vues Blade dans `resources/views`. Paramètres : `Settings/ProfileController` et `Settings/PasswordController`.
- Composants Blade maison (`x-ui.*`) et Tailwind CSS 4, build via Vite+ (`vp`).
- Pest 5, Pint et Larastan/PHPStan.
- Le calendrier est `CalendarController` + `resources/views/calendar.blade.php`, sur la route `dashboard`. Les cours passent par `CourseController` (routes ressource `courses`).
- Les modèles, enums, policies et notifications sont dans `app/`.

## Commandes utiles

```bash
composer setup                      # Installation complète
composer run dev                    # Serveur PHP + Vite + queue
php artisan db:seed                 # Données de démonstration
composer test                       # Pint + PHPStan + Pest
php artisan test                    # Tests seuls
vendor/bin/pint --dirty             # Formatage
```

## Comptes de démonstration

Mot de passe commun : `password`.

| Rôle | Email |
| --- | --- |
| Administrateur | `admin@univ.test` |
| Professeur | `awa.sossou@univ.test` |
| Professeur | `koffi.dossou@univ.test` |
| Étudiant | `alice.houngbo@univ.test` |
| Étudiant | `bob.adjovi@univ.test` |

## Couverture de tests actuelle

- Visibilité des cours selon le rôle et le groupe.
- Gestion des groupes par l'administrateur dans `tests/Feature/Admin/GroupTest.php`.
- Création d'un cours, autorisation, validation, conflit d'horaire, choix du groupe et notifications dans `tests/Feature/CourseCreationTddTest.php`.
- Enums backed avec des cas en TitleCase.
- Règles d'architecture.
- Modification, changement de groupe et annulation d'un cours dans `tests/Feature/CourseScheduleTest.php`.

## Limites connues

- Seule la semaine courante est affichée, sans navigation.
- Les conflits de salle ne sont pas vérifiés, seulement les chevauchements d'un même professeur.
- Les emails sont envoyés de façon synchrone, sans queue.
