# Exercice TDD : création d'un cours avec notifications

## Contexte

**Campus Planning** est une application Laravel de gestion d'emploi du temps universitaire. Un professeur peut créer un cours, y inscrire des étudiants et notifier les participants par email.

Vous allez reconstruire cette fonctionnalité en TDD, puis représenter son fonctionnement avec un diagramme de séquence.

## Objectifs pédagogiques

- Écrire un test qui échoue avant d'écrire le code de production.
- Passer au vert avec le minimum de code nécessaire.
- Refactorer sans changer le comportement.
- Décrire une interaction entre plusieurs objets avec un diagramme de séquence.

## Fonctionnalité à réaliser

Un professeur connecté ouvre le calendrier et crée un cours avec :

- un titre ;
- une salle ;
- un jour de la semaine ;
- une heure de début et une heure de fin ;
- une liste d'étudiants à inscrire.

Le cours est enregistré, les étudiants sont inscrits, et un email est envoyé au professeur et aux étudiants.

## Règles d'acceptation

1. **Création** : un professeur peut créer un cours. Le cours est enregistré avec son `professor_id`, les étudiants sélectionnés sont attachés, et la modale se ferme.
2. **Autorisation** : un étudiant ne peut pas créer de cours. L'accès est refusé avec une réponse 403.
3. **Validation** : le titre est obligatoire et limité à 255 caractères. La salle doit faire partie de la liste des salles. Le jour doit être compris entre lundi et samedi. Le cours commence à 08:00 au plus tôt, se termine à 20:00 au plus tard, et l'heure de début précède l'heure de fin.
4. **Conflit de professeur** : un professeur ne peut pas avoir deux cours qui se chevauchent le même jour. Deux cours qui se touchent, par exemple 08:00-10:00 puis 10:00-12:00, sont autorisés.
5. **Inscriptions** : seuls les utilisateurs ayant le rôle étudiant peuvent être inscrits.
6. **Notification** : à la création, un email est envoyé au professeur et à chaque étudiant inscrit. La notification porte le changement `CourseChange::Created`.
7. **Résilience** : si l'envoi d'email échoue, le cours est quand même enregistré. L'erreur est journalisée et l'utilisateur voit un avertissement.

Le contrôle de conflit ne porte que sur les cours du même professeur. La salle n'est pas encore contrôlée.

## Contrat technique

- Page : `dashboard`, composant Livewire `pages::calendar`.
- Méthode appelée à la soumission : `saveCourse()`.
- Champs du formulaire : `title`, `room`, `dayOfWeek`, `startTime`, `endTime`, `studentIds`.
- Enums disponibles : `App\Enums\Weekday`, `App\Enums\Room`, `App\Enums\CourseChange`.
- Notification : `App\Notifications\CourseScheduleNotification`.
- Tests : Pest, dans `tests/Feature/CourseCreationTest.php`.
- Utilisez les factories existantes (`User::factory()->professor()`, `User::factory()->student()`, `Course::factory()`).

## Démarche TDD attendue

Pour chaque comportement :

1. Choisissez un seul comportement à la fois.
2. Écrivez un test qui décrit ce comportement.
3. Lancez le test et vérifiez qu'il échoue pour la bonne raison.
4. Écrivez le code de production le plus simple possible.
5. Relancez le test jusqu'au vert.
6. Refactorez sans modifier le comportement.
7. Passez au comportement suivant.

Règles :

- Pas de code de production sans un test rouge.
- Un test ne vérifie qu'un seul comportement.
- Testez le comportement observable, pas les détails d'implémentation.
- N'écrivez pas tous les tests d'un coup.
- Utilisez `Notification::fake()` pour vérifier les emails.
- Lancez régulièrement la suite ciblée : `php artisan test --compact tests/Feature/CourseCreationTest.php`.

## Diagramme de séquence demandé

Produisez un diagramme de séquence pour la création d'un cours.

Participants attendus :

- le professeur ;
- le composant Livewire `pages::calendar` ;
- la politique `CoursePolicy` ;
- la base de données ;
- la notification `CourseScheduleNotification` ;
- le serveur email.

Le diagramme doit montrer :

- le chemin nominal complet, de la soumission du formulaire jusqu'au toast de succès ;
- le cas où l'utilisateur n'est pas professeur ;
- le cas où les données sont invalides ou le créneau est occupé ;
- le cas où l'email échoue, avec le cours conservé.

Utilisez des blocs `alt` et `else`, et indiquez clairement qui appelle qui. Le diagramme doit refléter le code réel, pas une version idéalisée.

Livrable : un fichier `diagramme-sequence.mmd` au format Mermaid, ou une image exportée.

## Livrables

- `diagramme-sequence.mmd` ou une image.
- `tests/Feature/CourseCreationTest.php`.
- Le code nécessaire pour faire passer les tests.
- Un court `journal-tdd.md` avec, pour chaque cycle : le test écrit, l'échec observé, le code ajouté, et le refactor éventuel.

## Barème indicatif

| Critère | Points |
| --- | --- |
| Le diagramme couvre le chemin nominal et les branches d'erreur | 4 |
| Les participants et les messages sont cohérents avec le code | 3 |
| Les tests décrivent des comportements observables | 4 |
| Les tests échouent avant l'implémentation et passent après | 3 |
| Les cycles TDD sont tracés dans le journal | 3 |
| Le code respecte les conventions du projet et passe Pint | 2 |
| La suite de tests passe | 1 |

## Conseils

- Commencez par le chemin nominal, puis ajoutez les règles une par une.
- Si un test passe dès le premier lancement, demandez-vous ce qu'il prouve vraiment.
- Pour le conflit d'horaire, testez aussi la borne : deux cours qui se touchent ne doivent pas être en conflit.
- Pour l'échec d'email, vérifiez que le cours existe toujours après l'appel.
- Un bon diagramme se lit sans le code : les messages doivent être des verbes compréhensibles.
